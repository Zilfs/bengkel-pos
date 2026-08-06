<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'transaction_id',
    'product_id',
    'product_name_snapshot',
    'price_snapshot',
    'quantity',
    'subtotal_snapshot',
])]
class TransactionProductItem extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (TransactionProductItem $item) {
            if (blank($item->product_name_snapshot) || blank($item->price_snapshot)) {
                $product = $item->product ?? \App\Models\Product::find($item->product_id);

                $item->product_name_snapshot ??= $product?->name;
                $item->price_snapshot ??= $product?->selling_price ?? 0;
            }

            $item->subtotal_snapshot = $item->price_snapshot * $item->quantity;
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
