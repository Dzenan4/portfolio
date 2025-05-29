<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'middle_initial',
        'last_name',
        'address_street',
        'address_city',
        'address_state',
        'address_zip',
        'phone_number',
        'dl_number',
        'dl_state',
        'dob',
        'dl_picture_link',
        'self_picture_link',
        'notes',
    ];

    public function transactions(): hasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
