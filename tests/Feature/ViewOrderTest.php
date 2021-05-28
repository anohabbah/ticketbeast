<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_view_their_order_confirmation(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'amount' => 2550,
            'card_last_four_number' => 1881,
        ]);

        /** @var Ticket $ticket */
        $ticket = Ticket::factory()->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE1234',
        ]);

        $response = $this->get("/orders/ORDERCONFIRMATION1234");
//        $response = $this->get("/orders/$order->confirmation_number");

        $response->assertStatus(Response::HTTP_OK);

        $response->assertViewHas('order', $order);

        $response->assertSee('ORDERCONFIRMATION1234');
        $response->assertSee('$25.50');
        $response->assertSee('**** **** **** 1881');
        $response->assertSee('TICKETCODE1234');
    }
}
