<?php

namespace App\Models;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Concert
 * @package App\Models
 *
 * @property string title
 * @property string subtitle
 * @property string ticket_price
 * @property float ticket_price_in_dollars
 * @property string city
 * @property string state
 * @property string zip
 * @property string venue
 * @property string venue_address
 * @property string additional_information
 *
 * @method published
 */
class Concert extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = ['date'];

    /**
     * Add formatted_date attribute to model
     * @return string
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('F j, Y');
    }

    /**
     * Add formatted_start_time attribute to model
     * @return string
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->date->format('g:ia');
    }

    /**
     * Add ticket_price_in_dollars attribute to model
     * @return string
     */
    public function getTicketPriceInDollarsAttribute(): string
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function orderTickets(string $email, int $ticketQuantity)
    {
        $tickets = $this->tickets()->available()->take($ticketQuantity)->get();
        if ($tickets->count() < $ticketQuantity) {
            throw new NotEnoughTicketsException();
        }

        $order = $this->orders()->create(['email' => $email]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function addTickets(int $quantity)
    {
        foreach (range(1, $quantity) as $i) {
            $this->tickets()->create([]);
        }
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    // RELATIONSHIPS

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // QUERY SCOPES

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }
}
