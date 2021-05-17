<?php


namespace App\Billing;


class FakePaymentGateway implements PaymentGateway
{
    private \Illuminate\Support\Collection $charges;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken(): string
    {
        return 'valid_test_token';
    }

    public function totalCharges(): int
    {
        return $this->charges->sum();
    }

    public function charge(int $amount, string $token): void
    {
        if ($token !== $this->getValidTestToken()) {
            throw new PaymentFailedException();
        }

        $this->charges[] = $amount;

    }
}
