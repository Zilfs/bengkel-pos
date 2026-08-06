<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_number')
                    ->searchable(),
                TextColumn::make('discount_amount')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->formatStateUsing(fn(string $state) => strtoupper($state))
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                    })
                    ->searchable(),
                TextColumn::make('delivery_status')
                    ->formatStateUsing(fn(string $state) => strtoupper($state))
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'delivered' => 'success',
                        'in_progress' => 'info',
                    })
                    ->searchable(),
                TextColumn::make('delivered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->hidden(fn(Transaction $record): bool => $record->payment_status === 'paid' &&  $record->delivery_status === 'delivered'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
