<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'transaction_id',
    'service_type_id',
    'mechanic_id',
    'service_name_snapshot',
    'price_snapshot',
    'commission_percentage_snapshot',
    'commission_amount_snapshot',
])]
class TransactionServiceItem extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (TransactionServiceItem $item) {
            if (blank($item->service_name_snapshot) || blank($item->price_snapshot) || $item->commission_percentage_snapshot === null) {
                $serviceType = $item->serviceType ?? \App\Models\ServiceType::find($item->service_type_id);

                $item->service_name_snapshot ??= $serviceType?->name;
                $item->price_snapshot ??= $serviceType?->default_price ?? 0;
                $item->commission_percentage_snapshot ??= $serviceType?->default_commission_percentage ?? 0;
            }

            $item->commission_amount_snapshot = round(
                $item->price_snapshot * $item->commission_percentage_snapshot / 100,
                2
            );
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
    }
}
