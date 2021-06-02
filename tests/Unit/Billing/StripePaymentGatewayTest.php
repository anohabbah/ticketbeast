<?php


namespace Tests\Unit\Billing;

use App\Billing\PaymentGateway;
use App\Billing\StripePaymentGateway;
use Tests\TestCase;

/**
 * Class StripePaymentGatewayTest
 * @package Tests\Unit\Billing
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    private function getPaymentGateway(): PaymentGateway
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }
}
