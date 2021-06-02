<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderConfirmationEmail;
use App\Models\Order;
use Tests\TestCase;

class OrderConfirmationEmailTest extends TestCase
{
    /** @test */
    public function email_contains_a_link_to_the_order_confirmation_page(): void
    {
        $order = Order::factory()->make(['confirmation_number' => 'ORDERCONFIRMATION1234']);

        $email = new OrderConfirmationEmail($order);

        $rendered = $email->render();

        self::assertStringContainsString(url('/orders/ORDERCONFIRMATION1234'), $rendered);
    }

    /** @test */
    public function email_has_a_subject(): void
    {
        $order = Order::factory()->make(['confirmation_number' => 'ORDERCONFIRMATION1234']);

        $email = new OrderConfirmationEmail($order);

        self::assertEquals('Your TicketBeast Order', $email->build()->subject);
    }
}
