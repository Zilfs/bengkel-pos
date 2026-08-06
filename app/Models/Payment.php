<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'transaction_id',
    'payment_method',
    'amount',
    'paid_at',
    'received_by',
    'notes',
])]
class Payment extends Model
{
    // tabel ini cuma punya created_at (tanpa updated_at), sesuai migration
    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            $payment->created_at ??= now();
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}