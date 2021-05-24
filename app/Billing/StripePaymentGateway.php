<?php

namespace App\Billing;


class StripePaymentGateway implements PaymentGateway
{

    /**
     * @var string
     */
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge(int $amount, string $token): void
    {
        $stripe = new \Stripe\StripeClient($this->apiKey);
        $stripe->charges->create([
            'amount' => $amount,
            'currency' => 'eur',
            'source' => $token,
        ]);
    }
}
