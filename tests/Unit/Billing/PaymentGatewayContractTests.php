<?php


namespace Tests\Unit\Billing;


use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;

trait PaymentGatewayContractTests
{
    abstract protected function getPaymentGateway(): PaymentGateway;

    /** @test */
    public function charges_with_a_valid_payment_token_are_successful(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });

        self::assertCount(1, $newCharges);
        self::assertEquals(2500, $newCharges->sum());
    }

    /**
     * @test
     */
    public function charges_with_invalid_payment_token_fail(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            try {
                $paymentGateway->charge(2500, 'invalid-token ');
            } catch (PaymentFailedException $e) {
                return;
            }

            self::fail('Charging with an invalid token did not throw an exception.');
        });

        self::assertCount(0, $newCharges);
    }

    /** @test */
    public function can_fetch_charges_created_during_a_callback(): void
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        self::assertCount(2, $newCharges);
        self::assertEquals([5000, 4000], $newCharges->all());
    }
}
