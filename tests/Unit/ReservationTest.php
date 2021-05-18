<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function calculating_total_cost(): void
    {
        $concert = Concert::factory()->create(['ticket_price' => 1200])->addTickets(3);
        $tickets = $concert->findTickets(3);

        $reservation = new Reservation($tickets);

        self::assertEquals(3600, $reservation->totalCost());
    }
}
