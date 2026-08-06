<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('transaction_number'),
                TextEntry::make('customer.name')
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label('Staff'),
                TextEntry::make('discount_amount')
                    ->money('IDR', locale: 'id_ID'),
                TextEntry::make('subtotal')
                    ->money('IDR', locale: 'id_ID'),
                TextEntry::make('total_amount')
                    ->money('IDR', locale: 'id_ID'),
                TextEntry::make('payment_status')
                    ->formatStateUsing(fn(string $state) => strtoupper($state))
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                    }),
                TextEntry::make('delivery_status')
                    ->formatStateUsing(fn(string $state) => strtoupper($state))
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'delivered' => 'success',
                        'in_progress' => 'info',
                    }),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('delivered_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),

                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Service Type')
                            ->schema([
                                RepeatableEntry::make('serviceItems')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('serviceType.name')
                                            ->label('Service Type'),
                                        TextEntry::make('mechanic.name')
                                            ->label('Mechanic'),
                                        TextEntry::make('price_snapshot')
                                            ->label('Price')
                                            ->money('IDR', locale: 'id_ID'),
                                        TextEntry::make('commission_percentage_snapshot')
                                            ->label('Commission')
                                            ->suffix('%'),
                                        TextEntry::make('commission_amount_snapshot')
                                            ->label('Comission (Rp)')
                                            ->money('IDR', locale: 'id_ID'),
                                    ])
                                    ->columns(5),
                            ]),

                        Tab::make('Product / Sparepart')
                            ->schema([
                                RepeatableEntry::make('productItems')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('product.name')
                                            ->label('Sparepart'),
                                        TextEntry::make('quantity'),
                                        TextEntry::make('price_snapshot')
                                            ->label('Price per item')
                                            ->money('IDR', locale: 'id_ID'),
                                        TextEntry::make('subtotal_snapshot')
                                            ->label('Subtotal')
                                            ->money('IDR', locale: 'id_ID'),
                                    ])
                                    ->columns(4),
                            ]),
                    ]),
            ]);
    }
}
