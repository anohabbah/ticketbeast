<?php

namespace App\Models;

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

    // RELATIONSHIPS

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // QUERY SCOPES

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }
}
