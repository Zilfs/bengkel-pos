<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                // Gunakan model Transaction pada parameter closure untuk mendapatkan data saat ini
                ->hidden(
                    fn(Transaction $record): bool =>
                    $record->payment_status === 'paid' && $record->delivery_status === 'delivered'
                ),
        ];
    }
}
