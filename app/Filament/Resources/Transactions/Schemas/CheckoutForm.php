<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Transaction;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class CheckoutForm
{
    public static function configure(Schema $schema, Transaction $transaction): Schema
    {
        return $schema
            ->components([
                Placeholder::make('subtotal_display')
                    ->label('Subtotal')
                    ->content(Number::currency($transaction->subtotal, 'IDR', locale: 'id')),

                Placeholder::make('discount_display')
                    ->label('Diskon')
                    ->content(Number::currency($transaction->discount_amount, 'IDR', locale: 'id')),

                Placeholder::make('total_display')
                    ->label('Total Tagihan')
                    ->content(Number::currency($transaction->total_amount, 'IDR', locale: 'id')),
                Repeater::make('payments')
                    ->relationship('payments')
                    ->label('Pembayaran')
                    ->schema([
                        Select::make('payment_method')
                            ->label('Metode')
                            ->options([
                                'cash' => 'Cash',
                                'qris' => 'QRIS',
                                'transfer' => 'Transfer',
                                'debit' => 'Debit',
                            ])
                            ->native(false)
                            ->required(),

                        TextInput::make('amount')
                            ->label('Nominal')
                            ->numeric()
                            ->minValue(1)
                            ->prefix('Rp')
                            ->required()
                            ->live(onBlur: true), // supaya Placeholder sisa tagihan di bawah ikut ter-update

                        DateTimePicker::make('paid_at')
                            ->label('Tanggal Bayar')
                            ->native(false)
                            ->default(now())
                            ->required(),

                        Select::make('received_by')
                            ->label('Diterima Oleh')
                            ->options(User::query()->pluck('name', 'id'))
                            ->native(false)
                            ->default(fn () => Auth::id())
                            ->required(),
                    ])
                    ->columns(4)
                    ->reorderable(false)
                    ->addActionLabel('Tambah Pembayaran')
                    ->defaultItems(0)
                    ->live(),

                Placeholder::make('remaining_display')
                    ->label('Sisa yang Perlu Dibayar')
                    ->content(function (Get $get) use ($transaction): string {
                        $paid = collect($get('payments') ?? [])->sum(fn ($row) => (float) ($row['amount'] ?? 0));
                        $remaining = $transaction->total_amount - $paid;

                        return Number::currency($remaining, 'IDR', locale: 'id');
                    }),
            ]);
    }
}