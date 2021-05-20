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
        $tickets = $this->findTickets($ticketQuantity);

        return $this->createOrder($email, $tickets);
    }

    /**
     * @param int $ticketQuantity
     * @return mixed
     */
    public function findTickets(int $ticketQuantity)
    {
        $tickets = $this->tickets()->available()->take($ticketQuantity)->get();
        if ($tickets->count() < $ticketQuantity) {
            throw new NotEnoughTicketsException();
        }

        return $tickets;
    }

    public function reserveTickets(int $quantity)
    {
        return $this->findTickets($quantity);
    }

    /**
     * @param string $email
     * @param $tickets
     * @return Model
     */
    public function createOrder(string $email, $tickets): Model
    {
        return Order::forTickets($tickets, $email, $tickets->sum('price'));
    }

    public function addTickets(int $quantity)
    {
        foreach (range(1, $quantity) as $i) {
            $this->tickets()->create([]);
        }

        return $this;
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    public function hasOrderFor(string $customerEmail): bool
    {
        return $this->orders()->where('email', $customerEmail)->count() > 0;
    }

    public function orderFor(string $customerEmail): \Illuminate\Database\Eloquent\Collection
    {
        return $this->orders()->where('email', $customerEmail)->get();
    }

    // RELATIONSHIPS

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'tickets');
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
