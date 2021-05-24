<?php


namespace Tests\Unit\Billing;


use App\Billing\StripePaymentGateway;
use Tests\TestCase;

class StripePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charges_with_valid_payment_token_are_successful(): void
    {
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        $stripe = new \Stripe\StripeClient(
            config('services.stripe.secret')
        );
        $token = $stripe->tokens->create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123',
            ],
        ]);

        $paymentGateway->charge(2500, $token->id);

        $lastCharge = $stripe->charges->all(['limit' => 1])['data'][0];

        self::assertEquals(2500, $lastCharge->amount);
    }
}
