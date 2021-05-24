<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Reservation;
use Mockery;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    /** @test */
    public function calculating_total_cost(): void
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 2000],
            (object) ['price' => 4500],
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        self::assertEquals(7700, $reservation->totalCost());
    }

    /** @test */
    public function retrieving_the_reservation_tickets(): void
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 2000],
            (object) ['price' => 4500],
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        self::assertEquals($tickets, $reservation->tickets());
    }

    /** @test */
    public function retrieving_the_customer_email(): void
    {
        $tickets = collect();

        $reservation = new Reservation($tickets, 'john@example.com');

        self::assertEquals('john@example.com', $reservation->email());
    }

    /** @test */
    public function reserved_tickets_are_release_when_a_reservation_is_canceled(): void
    {
        $tickets = collect([
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        $reservation->cancel();
    }
}
