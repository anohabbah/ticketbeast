<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use App\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function creating_order_from_tickets_email_and_amount(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create()->addTickets(5);

        $order = Order::forTickets($concert->reserveTickets(3, 'jane@example.com' )->tickets(), 'jane@example.com', 3600);

        self::assertEquals('jane@example.com', $order->email);
        self::assertEquals(3, $order->ticketQuantity());
        self::assertEquals(3600, $order->amount);
        self::assertEquals(2, $concert->ticketsRemaining());
    }

    /** @test */
    public function converting_to_an_array(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create(['ticket_price' => 3250])->addTickets(10);
        $order = $concert->orderTickets('jane@example.com', 5);

        self::assertEquals([
            'email' => 'jane@example.com',
            'ticket_quantity' => 5,
            'amount' => 16250,
        ], $order->toArray());
    }
}
