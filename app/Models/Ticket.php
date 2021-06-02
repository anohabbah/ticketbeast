<?php

namespace App\Models;

use App\Facades\TicketCode;
use Carbon\Carbon;
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
 * @property int id
 */
class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function release()
    {
        $this->update(['reserved_at' => null]);
    }

    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }

    public function claimFor(Order $order)
    {
        $this->code = TicketCode::generateFor($this);
        $order->tickets()->save($this);
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
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }
}
