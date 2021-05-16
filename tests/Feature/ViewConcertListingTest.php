<?php

namespace Tests\Feature;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_a_concert_list(): void
    {
        $this->withoutExceptionHandling();
        $concert = Concert::create([
            'title' => 'The Red Chord',
            'subtitle' => 'With animosity ans Lethargy',
            'date' => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The most Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
            'additional_information' => 'For tickets, call (555) 555-5555.',
        ]);

        $response = $this->get('/concerts/' . $concert->id);

        $response->assertSee('The Red Chord');
        $response->assertSee('With animosity ans Lethargy');
        $response->assertSee('December 13, 2016');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('The most Pit');
        $response->assertSee('123 Example Lane');
        $response->assertSee('Laraville, ON 17916');
        $response->assertSee('For tickets, call (555) 555-5555.');
    }
}
