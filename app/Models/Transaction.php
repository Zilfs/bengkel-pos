<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(TransactionServiceItem::class);
    }

    public function productItems(): HasMany
    {
        return $this->hasMany(TransactionProductItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
