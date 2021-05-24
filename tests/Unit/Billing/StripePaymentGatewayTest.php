<?php


namespace Tests\Unit\Billing;


use App\Billing\StripePaymentGateway;
use Tests\TestCase;

/**
 * Class StripePaymentGatewayTest
 * @package Tests\Unit\Billing
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripe = new \Stripe\StripeClient(
            config('services.stripe.secret')
        );
        $this->lastCharge = $this->lastCharge();
    }

    /** @test */
    public function charges_with_valid_payment_token_are_successful(): void
    {
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        $paymentGateway->charge(2500, $this->validToken());

        self::assertCount(1, $this->newCharges());
        self::assertEquals(2500, $this->lastCharge()->amount);
    }

    /**
     * @var \Stripe\StripeClient
     */
    private $stripe;
    /**
     * @var mixed
     */
    private $lastCharge;

    /**
     * @param \Stripe\StripeClient $stripe
     * @return mixed
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function lastCharge()
    {
        return $this->stripe->charges->all(['limit' => 1])->first();
    }

    private function newCharges()
    {
        return $this->stripe->charges->all([
            'limit' => 1,
            'ending_before' => $this->lastCharge->id,
        ])['data'];
    }

    /**
     * @param \Stripe\StripeClient $stripe
     * @return string
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function validToken(): string
    {
        $token = $this->stripe->tokens->create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123',
            ],
        ]);
        return $token->id;
    }
}
