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
        $concert = Concert::factory()->create([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $date = $concert->formatted_date;

        self::assertEquals('December 1, 2016', $date);
    }
}
