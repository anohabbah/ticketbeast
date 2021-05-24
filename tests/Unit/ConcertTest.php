<?php

namespace Tests\Unit;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcertTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function can_get_formatted_date(): void
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        self::assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    public function can_get_formatted_start_time(): void
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        self::assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars(): void
    {
        $concert = Concert::factory()->make([
            'ticket_price' => '9503',
        ]);

        self::assertEquals('95.03', $concert->ticket_price_in_dollars);
    }

    /** @test */
    public function concerts_with_a_published_at_date_are_published(): void
    {
        $publishedConcertA = Concert::factory()->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB = Concert::factory()->create(['published_at' => Carbon::parse('-1 week')]);
        $unpublishedConcert = Concert::factory()->create(['published_at' => null]);

        $publishedConcerts = Concert::published()->get();

        self::assertTrue($publishedConcerts->contains($publishedConcertA));
        self::assertTrue($publishedConcerts->contains($publishedConcertB));
        self::assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */
    public function can_order_concert_tickets(): void
    {
        $concert = Concert::factory()->create()->addTickets(3);

        $order = $concert->orderTickets('jane@example.com', 3);

        self::assertEquals('jane@example.com', $order->email);
        self::assertEquals(3, $order->ticketQuantity());
    }

    /** @test */
    public function can_add_tickets(): void
    {
        $concert = Concert::factory()->create()->addTickets(50);
        self::assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create()->addTickets(50);
        $concert->orderTickets('jane@example.com', 30);

        self::assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    public function trying_to_purchase_more_tickets_than_remain_throws_an_exception(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create()->addTickets(10);
        try {
            $concert->orderTickets('jane@example.com', 11);
        } catch (NotEnoughTicketsException $e) {
            self::assertFalse($concert->hasOrderFor('jane@example.com'));
            self::assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        self::fail('Order succeed even though there were not enough tickets remaining.');
    }

    /** @test */
    public function cannot_order_tickets_that_have_already_been_purchased(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create()->addTickets(10);
        $concert->orderTickets('jane@example.com', 8);

        try {
            $concert->orderTickets('john@example.com', 3);
        } catch (NotEnoughTicketsException $e) {
            self::assertFalse($concert->hasOrderFor('john@example.com'));
            self::assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        self::fail('Order succeed even though there were not enough tickets remaining.');
    }

    /** @test */
    public function can_reserve_available_tickets(): void
    {
        $concert = Concert::factory()->create()->addTickets(3);
        self::assertEquals(3, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2, 'john@example.com');

        self::assertCount(2, $reservation->tickets());
        self::assertEquals('john@example.com', $reservation->email());
        self::assertEquals(1, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_purchased(): void
    {
        $concert = Concert::factory()->create()->addTickets(3);
        $concert->orderTickets('jane@example.com', 2);

        try {
            $concert->reserveTickets(2, 'john@example.com');
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        self::fail('Reserving tickets succeed even though they were already sold.');
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_reserved(): void
    {
        $concert = Concert::factory()->create()->addTickets(3);
        $concert->reserveTickets(2, 'jane@example.com');

        try {
            $concert->reserveTickets(2, 'john@example.com');
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        self::fail('Reserving tickets succeed even though they were already reserved.');
    }
}
