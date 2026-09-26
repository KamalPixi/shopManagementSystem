<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyCategory extends Model
{
    protected $guarded = [];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function moneyMovements()
    {
        return $this->hasMany(MoneyMovement::class);
    }
}
