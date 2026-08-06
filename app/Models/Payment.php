<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'transaction_id',
    'payment_method', // typo 'payement_method' diperbaiki
    'amount',
    'paid_at',
    'received_by',
    'notes',
])]
class Payment extends Model
{
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
