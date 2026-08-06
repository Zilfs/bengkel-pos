<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'type',
    'quantity',
    'transaction_product_item_id',
    'notes',
    'created_by',
])]
class StockMovement extends Model
{
    // tabel ini cuma punya created_at (tanpa updated_at), sesuai migration
    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (StockMovement $movement) {
            $movement->created_at ??= now();
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transactionProductItem(): BelongsTo
    {
        return $this->belongsTo(TransactionProductItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}