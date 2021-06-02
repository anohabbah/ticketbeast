<?php

namespace App\Billing;

use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

class StripePaymentGateway implements PaymentGateway
{
    public const TEST_CARD_NUMBER = '4242424242424242';

    /**
     * @var \Stripe\StripeClient
     */
    private $stripe;

    public function __construct(string $apiKey)
    {
        $this->stripe = new \Stripe\StripeClient($apiKey);
    }

    public function charge(int $amount, string $token)
    {
        try {
            $stripeCharge = $this->stripe->charges->create([
                'amount' => $amount,
                'currency' => 'eur',
                'source' => $token,
            ]);

            return new Charge([
                'amount' => $stripeCharge->amount,
                'card_last_four' => $stripeCharge->source->last4,
            ]);
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException();
        }
    }

    public function getValidTestToken(string $cardNumber = self::TEST_CARD_NUMBER): string
    {
        $token = $this->stripe->tokens->create([
            'card' => [
                'number' => $cardNumber,
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123',
            ],
        ]);

        return $token->id;
    }

    public function newChargesDuring($callback)
    {
        $latestCharge = $this->lastCharge();

        $callback($this);

        return $this->newChargesSince($latestCharge)->map(function (\Stripe\Charge $stripeCharge) {
            return new Charge([
                'amount' => $stripeCharge->amount,
                'card_last_four' => $stripeCharge->source->last4,
            ]);
        });
    }

    /**
     * @return mixed
     * @throws ApiErrorException
     */
    private function lastCharge()
    {
        return $this->stripe->charges->all(['limit' => 1])->first();
    }

    private function newChargesSince(\Stripe\Charge $charge = null)
    {
        return collect($this->stripe->charges->all([
            'ending_before' => $charge->id ?? null,
        ])['data']);
    }
}
