<?php

namespace Tests\Unit;

use App\Facades\TicketCode;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_ticket_can_be_reserved(): void
    {
        $ticket = Ticket::factory()->create();

        $ticket->reserve();

        self::assertNotNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function a_ticket_can_be_release(): void
    {
        $ticket = Ticket::factory()->reserved()->create();

        self::assertNotNull($ticket->reserved_at);

        $ticket->release();

        self::assertNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function a_ticket_can_be_claimed_for_an_order(): void
    {
        $order = Order::factory()->create();
        $ticket = Ticket::factory()->create(['code' => null]);
        TicketCode ::shouldReceive('generateFor')->andReturn('TICKETCODE1');

        $ticket->claimFor($order);

        self::assertContains($ticket->id, $order->tickets->pluck('id'));
        self::assertEquals('TICKETCODE1', $ticket->code);
    }
}
