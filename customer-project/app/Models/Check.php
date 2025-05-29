<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    protected $fillable = [
        'company_name',
        'company_address_street',
        'company_address_city',
        'company_address_state',
        'company_address_zip',
        'account_number',
        'routing_number',
        'type',
        'cashing_status',
        'notes',
    ];

    public function transactions(): hasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
