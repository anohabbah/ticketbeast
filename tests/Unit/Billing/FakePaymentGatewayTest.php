<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
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
}
