<?php

namespace App\Models;

use App\Facades\OrderConfirmationNumber;
use App\OrderConfirmationNumberGenerator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Order
 * @package App\Models
 *
 * @property int id
 * @property int amount
 * @property string email
 * @property string confirmation_number
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Collection tickets
 * @property Concert concert
 */
class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function forTickets($tickets, string $email, int $amount): Order
    {
        $order = self::create([
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'email' => $email,
            'amount' => $amount,
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function ticketQuantity(): int
    {
        return $this->tickets()->count();
    }

    public function toArray(): array
    {
        return [
            'confirmation_number' => $this->confirmation_number,
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount,
        ];
    }

    public static function findByConfirmationNumber($confirmationNumber)
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }

    // RELATIONSHIPS

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function concert(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }
}
