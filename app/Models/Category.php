<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public function subcategories()
    {
        return $this->hasMany(Category::class, 'category_id');
    }

    // Define the relationship for the parent category
    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
