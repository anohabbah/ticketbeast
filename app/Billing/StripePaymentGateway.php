<?php

namespace App\Billing;


use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

class StripePaymentGateway implements PaymentGateway
{

    /**
     * @var \Stripe\StripeClient
     */
    private $stripe;

    public function __construct(string $apiKey)
    {
        $this->stripe = new \Stripe\StripeClient($apiKey);
    }

    public function charge(int $amount, string $token): void
    {
        try {
            $this->stripe->charges->create([
                'amount' => $amount,
                'currency' => 'eur',
                'source' => $token,
            ]);
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException();
        }
    }

    public function getValidTestToken(): string
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

    public function newChargesDuring($callback)
    {
        $latestCharge = $this->lastCharge();

        $callback($this);

        return $this->newChargesSince($latestCharge)->pluck('amount');
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
