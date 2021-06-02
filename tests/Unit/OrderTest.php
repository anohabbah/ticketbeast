<?php

namespace Tests\Unit;

use App\Billing\Charge;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function PHPUnit\Framework\assertEquals;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function creating_order_from_tickets_email_and_amount(): void
    {
        $charge = new Charge(['amount' => 3600, 'card_last_four' => '1234']);
        $tickets = collect([
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
        ]);

        $order = Order::forTickets($tickets, 'jane@example.com', $charge);

        self::assertEquals('jane@example.com', $order->email);
        self::assertEquals(3600, $order->amount);
        self::assertEquals('1234', $order->card_last_four_number);
        $tickets->each->shouldHaveReceived('claimFor', [$order]);
    }

    /** @test */
    public function retrieving_an_order_by_its_confirmation_number(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'confirmation_number' => 'CONFIRMATIONNUMBER',
        ]);

        $orderFound = Order::findByConfirmationNumber('CONFIRMATIONNUMBER');

        assertEquals($order->id, $orderFound->id);
    }

    /** @test */
    public function retrieving_a_nonexistent_order_by_confirmation_number_throws_an_exception(): void
    {
        try {
            Order::findByConfirmationNumber('CONFIRMATIONNUMBER');
        } catch (ModelNotFoundException $e) {
            self::assertNull(null);

            return;
        }

        fail('No matching order was found for the specified confirmation number, but no exception thrown.');
    }

    /** @test */
    public function converting_to_an_array(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => 'jane@example.com',
            'amount' => 6000,
        ]);
        $order->tickets()->saveMany([
            Ticket::factory()->create(['code' => 'TICKETCODE1']),
            Ticket::factory()->create(['code' => 'TICKETCODE2']),
            Ticket::factory()->create(['code' => 'TICKETCODE3']),
        ]);

        self::assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => 'jane@example.com',
            'amount' => 6000,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ],
        ], $order->toArray());
    }
}
