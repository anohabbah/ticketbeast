<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
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
    public function customer_can_purchase_tickets_to_a_published_concert(): void
    {
        $concert = Concert::factory()->published()->create(['ticket_price' => 3250])
            ->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'email' => 'john@example.com',
                'ticket_quantity' => 3,
                'amount' => 9750,
            ]);

        self::assertEquals(9750, $this->paymentGateway->totalCharges());
        self::assertTrue($concert->hasOrderFor('john@example.com'));
        self::assertEquals(3, $concert->orderFor('john@example.com')->first()->ticketQuantity());
    }

    /** @test */
    public function cannot_purchase_tickets_to_an_unpublished_concert(): void
    {
        $concert = Concert::factory()->unpublished()->create();

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        self::assertFalse($concert->hasOrderFor('john@example.com'));
        self::assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test */
    public function an_order_is_not_created_if_payment_fail(): void
    {
        $concert = Concert::factory()->published()->create(['ticket_price' => 3250]);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-test-token',
        ]);

        $response->assertStatus(422);

        self::assertFalse($concert->hasOrderFor('john@example.com'));
    }

    /** @test */
    public function cannot_purchase_more_tickets_than_remain(): void
    {
        $concert = Concert::factory()->published()->create()
            ->addTickets(50);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 52,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertFalse($concert->hasOrderFor('john@example.com'));
        self::assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test */
    public function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase(): void
    {
        $concert = Concert::factory()->published()->create(['ticket_price' => 1200])
            ->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function() use ($concert) {
            $response = $this->orderTickets($concert, [
                'email' => 'personA@example.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]);

            $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
            self::assertFalse($concert->hasOrderFor('personB@example.com'));
            self::assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $this->orderTickets($concert, [
            'email' => 'personA@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        self::assertEquals(3600, $this->paymentGateway->totalCharges());
        self::assertTrue($concert->hasOrderFor('personA@example.com'));
        self::assertEquals(3, $concert->orderFor('personA@example.com')->first()->ticketQuantity());
    }

    /** @test */
    public function email_is_required_to_purchases_tickets(): void
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function email_must_be_valid_to_purchases_tickets(): void
    {
        $concert = Concert::factory()->published()->create();

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
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets($concert, [
            'email' => 'john@zeample.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'ticket_quantity');

    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1_to_purchases_tickets(): void
    {
        $concert = Concert::factory()->published()->create();

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
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets($concert, [
            'email' => 'john@zeample.com',
            'ticket_quantity' => 3,
        ]);

        $this->assertValidationError($response, 'payment_token');
    }
}

