<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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
                $product = $item->product ?? Product::find($item->product_id);

                $item->product_name_snapshot ??= $product?->name;
                $item->price_snapshot ??= $product?->selling_price ?? 0;
            }

            $item->subtotal_snapshot = $item->price_snapshot * $item->quantity;
        });

        // baris baru masuk nota -> stok langsung berkurang
        static::created(function (TransactionProductItem $item) {
            static::adjustStock($item, $item->product_id, -$item->quantity, 'sale_out', "Terpakai di transaksi #{$item->transaction_id}");
        });

        // qty diubah, atau sparepart-nya diganti -> stok disesuaikan selisihnya saja
        static::updated(function (TransactionProductItem $item) {
            if ($item->wasChanged('product_id')) {
                $originalProductId = $item->getOriginal('product_id');
                $originalQuantity = (int) $item->getOriginal('quantity');

                // kembalikan stok produk lama sepenuhnya
                static::adjustStock($item, $originalProductId, $originalQuantity, 'adjustment', "Sparepart diganti pada transaksi #{$item->transaction_id}");
                // kurangi stok produk baru sepenuhnya
                static::adjustStock($item, $item->product_id, -$item->quantity, 'adjustment', "Sparepart diganti pada transaksi #{$item->transaction_id}");

                return;
            }

            if ($item->wasChanged('quantity')) {
                $delta = $item->quantity - (int) $item->getOriginal('quantity');

                if ($delta !== 0) {
                    static::adjustStock($item, $item->product_id, -$delta, 'adjustment', "Perubahan qty pada transaksi #{$item->transaction_id}");
                }
            }
        });

        // item dibatalkan (soft delete) -> stok dikembalikan penuh
        static::deleted(function (TransactionProductItem $item) {
            static::adjustStock($item, $item->product_id, $item->quantity, 'return_in', "Dibatalkan dari transaksi #{$item->transaction_id}");
        });
    }

    // $delta negatif = stok berkurang, $delta positif = stok bertambah
    private static function adjustStock(TransactionProductItem $item, int $productId, int $delta, string $type, string $notes): void
    {
        if ($delta === 0) {
            return;
        }

        if ($delta > 0) {
            Product::where('id', $productId)->increment('stock_quantity', $delta);
        } else {
            Product::where('id', $productId)->decrement('stock_quantity', abs($delta));
        }

        StockMovement::create([
            'product_id' => $productId,
            'type' => $type,
            'quantity' => abs($delta),
            'transaction_product_item_id' => $item->id,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
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