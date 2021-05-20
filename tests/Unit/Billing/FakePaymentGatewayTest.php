<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase {
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful(): void
    {
        $paymentGateway = new FakePaymentGateway();

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

       self::assertEquals(2500, $paymentGateway->totalCharges());
    }

    /**
     * @test
     */
    public function charges_with_invalid_payment_token_fail(): void
    {
        try {
            $paymentGateway = new FakePaymentGateway();
            $paymentGateway->charge(2500, 'invalid-token ');
        } catch (PaymentFailedException $e) {
            self::assertTrue(true);
            return;
        }
        self::fail();
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
