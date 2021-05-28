<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    private function getPaymentGateway(): PaymentGateway
    {
        return new FakePaymentGateway();
    }

    /** @test */
    public function running_a_hook_before_the_first_charge(): void
    {
        $paymentGateway = new FakePaymentGateway();
        $callbackRan = 0;

        $paymentGateway->beforeFirstCharge(function (PaymentGateway $paymentGateway) use (&$callbackRan) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $callbackRan++;
            self::assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        self::assertEquals(1, $callbackRan);
        self::assertEquals(5000, $paymentGateway->totalCharges());
    }
}
