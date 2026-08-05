<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'transaction_number',
    'customer_id',
    'user_id',
    'discount_amount',
    'subtotal',
    'total_amount',
    'payment_status',
    'delivery_status',
    'notes',
    'delivered_at',
])]
class Transaction extends Model
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
