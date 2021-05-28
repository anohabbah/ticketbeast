<?php


namespace App\Billing;


use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{
    public const TEST_CARD_NUMBER = '4242424242424242';

    /** @var \Illuminate\Support\Collection $charges */
    private $charges;
    private $tokens;
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER): string
    {
        $token = 'fake-tok_' . Str::random(24);
        $this->tokens[$token] = $cardNumber;
        return $token;
    }

    public function totalCharges(): int
    {
        return $this->charges->map->amount()->sum();
    }

    public function charge(int $amount, string $token)
    {
        if ($this->beforeFirstChargeCallback !== null) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if (! $this->tokens->has($token)) {
            throw new PaymentFailedException();
        }

        return $this->charges[] = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens->get($token), -4),
        ]);
    }

    public function beforeFirstCharge(\Closure $callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }

    public function newChargesDuring($callback)
    {
        $chargesFrom = $this->charges->count();

        $callback($this);

        return $this->charges->slice($chargesFrom)->reverse()->values();
    }
}
