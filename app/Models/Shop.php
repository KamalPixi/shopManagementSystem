<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $guarded = [];

    protected $casts = [
        'currency' => 'array',
        'settings' => 'array',
    ];

    protected $fillable = [
        'name',
        'logo',
        'address',
        'state',
        'city',
        'postal_code',
        'country',
        'contact_no',
        'email',
        'gst_percent',
        'settings',
        'currency',
        'timezone',
    ];
}
