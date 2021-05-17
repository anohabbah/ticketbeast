<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    // SCOPES

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereNull('order_id');
    }
}
