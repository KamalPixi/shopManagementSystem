<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public function attributeValues() {
        return $this->hasMany(AttributeValue::class);
    }
}
