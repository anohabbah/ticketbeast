<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Ticket
 * @package App\Models
 *
 * @property Concert concert
 * @property int price
 */
class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function release()
    {
        $this->update(['order_id' => null]);
    }

    // ACCESSORS AND MUTATORS
    public function getPriceAttribute(): int
    {
        return$this->concert->ticket_price;
    }


    // RELATIONSHIPS

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    // SCOPES

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereNull('order_id');
    }
}
