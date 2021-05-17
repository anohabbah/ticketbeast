<?php

namespace Tests\Unit;


use App\Models\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_ticket_can_be_release(): void
    {
        $concert = Concert::factory()->create();
        $concert->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $concert->tickets()->first();
        self::assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        self::assertNull($ticket->fresh()->order_id);
    }
}
