<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'contact_person',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'bank_details',
        'notes',
        'website',
    ];
}
