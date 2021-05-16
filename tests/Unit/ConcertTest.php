<?php

namespace Tests\Unit;

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
    public function concerts_with_published_at_date_are_published(): void
    {
        $publishedConcertA = Concert::factory()->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB = Concert::factory()->create(['published_at' => Carbon::parse('-1 week')]);
        $unpublishedConcert = Concert::factory()->create(['published_at' => null]);

        $publishedConcerts = Concert::published()->get();

        self::assertTrue($publishedConcerts->contains($publishedConcertA));
        self::assertTrue($publishedConcerts->contains($publishedConcertB));
        self::assertFalse($publishedConcerts->contains($unpublishedConcert));
    }
}
