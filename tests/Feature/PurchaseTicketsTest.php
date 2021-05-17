<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    public function orderTickets($concert, $params): TestResponse
    {
        return $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    /**
     * @param TestResponse $response
     * @param string $field
     * @throws \Throwable
     */
    private function assertValidationError(TestResponse $response, string $field): void
    {
        $response->assertStatus(422);
        self::assertArrayHasKey($field, $response->decodeResponseJson()['errors']);
    }

    /** @test */
    public function customer_can_purchase_concert_tickets(): void
    {
        $concert = Concert::factory()->create(['ticket_price' => 3250]);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(201);

        self::assertEquals(9750, $this->paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        self::assertNotNull($order);
        self::assertEquals(3, $order->tickets->count());
    }

    /** @test */
    public function email_is_required_to_purchases_tickets(): void
    {
        $concert = Concert::factory()->create();

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function email_must_be_valid_to_purchases_tickets(): void
    {
        $concert = Concert::factory()->create();

        $response = $this->orderTickets($concert, [
            'email' => 'in_valid_email',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchases_tickets(): void
    {
        $concert = Concert::factory()->create();

        $response = $this->orderTickets($concert, [
            'email' => 'john@zeample.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'ticket_quantity');

    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1_to_purchases_tickets(): void
    {
        $concert = Concert::factory()->create();

        $response = $this->orderTickets($concert, [
            'email' => 'john@zeample.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
            'ticket_quantity' => 0 ,
        ]);

        $this->assertValidationError($response, 'ticket_quantity');

    }

    /** @test */
    public function payment_token_is_required_to_purchases_tickets(): void
    {
        $concert = Concert::factory()->create();

        $response = $this->orderTickets($concert, [
            'email' => 'john@zeample.com',
            'ticket_quantity' => 3,
        ]);

        $this->assertValidationError($response, 'payment_token');
    }
}

