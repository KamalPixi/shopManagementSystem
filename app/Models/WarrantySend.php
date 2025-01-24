<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantySend extends Model
{
    protected $casts = [
        'customer_details' => 'array',
        'repair_cost' => 'decimal:2',
        'repair_cost_paid' => 'decimal:2',
        'repair_cost_due' => 'decimal:2',
    ];

    protected $fillable = [
        'sale_id',
        'reference_no',
        'product_name',
        'problem_description',
        'repair_shop_details',
        'customer_details',
        'repair_cost',
        'repair_cost_paid',
        'repair_cost_due',
        'send_date',
        'receive_date',
        'receive_remark',
        'customer_receive_date',
        'customer_receive_remark',
        'remark',
        'status',
    ];
}
