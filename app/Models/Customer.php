<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone_no',
        'email',
        'address',
        'state',
        'city',
        'postal_code',
        'dob',
    ];

    protected $casts = [
        'dob' => 'date',
    ];
}
