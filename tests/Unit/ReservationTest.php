<?php

namespace Tests\Unit;

use App\Reservation;
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

        $reservation = new Reservation($tickets);

        self::assertEquals(7700, $reservation->totalCost());
    }
}
