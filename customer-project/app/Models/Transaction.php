<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'customer_id',
        'check_id',
        'date',
        'check_amount',
        'payout_amount',
        'charge_amount',
        'charge_percentage',
        'check_number',
        'check_picture_link'
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function check(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Check::class);
    }
}
