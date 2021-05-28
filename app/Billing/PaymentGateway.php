<?php


namespace App\Billing;


interface PaymentGateway
{
    public function charge(int $amount, string $token);

    public function getValidTestToken(string $cardNumber);

    public function newChargesDuring($callback);
}
