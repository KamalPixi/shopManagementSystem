<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemMeta extends Model
{
    protected $table = 'system_meta';

    protected $fillable = [
        'is_installed',
        'is_db_seeded',
    ];
}
