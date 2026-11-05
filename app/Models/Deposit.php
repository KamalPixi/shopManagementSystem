<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'item_description',
        'total_amount',
        'deposit_amount',
        'remaining_balance',
        'payment_method',
        'payment_reference',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function generateNextReferenceNo(): string
    {
        $maxId = static::max('id') ?? 0;
        $next = $maxId + 1;
        do {
            $ref = sprintf('DEP-%06d', $next);
            $exists = static::where('reference_no', $ref)->exists();
            $next++;
        } while ($exists);

        return $ref;
    }
}
