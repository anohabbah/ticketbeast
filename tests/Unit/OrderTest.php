<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
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
        $concert = Concert::factory()->create(['ticket_price' => 1200])->addTickets(5);

        $order = Order::forTickets($concert->findTickets(3), 'jane@example.com');

        self::assertEquals('jane@example.com', $order->email, 3600);
        self::assertEquals(3, $order->ticketQuantity());
        self::assertEquals(3600, $order->amount);
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

    /** @test */
    public function tickets_are_released_when_an_order_is_cancelled(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create()->addTickets(10);

        $order = $concert->orderTickets('jane@example.com', 5);
        self::assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        self::assertEquals(10, $concert->ticketsRemaining());
        self::assertNull(Order::find($order->id));
    }
}
