<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Transaction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TransactionForm
{
    protected static function generateTransactionNumber(): string
    {
        $prefix = 'TRX-' . now()->format('Ymd') . '-';

        $lastNumber = Transaction::where('transaction_number', 'like', "{$prefix}%")
            ->orderByDesc('transaction_number')
            ->value('transaction_number');

        $nextIncrement = $lastNumber
            ? ((int) substr($lastNumber, -4)) + 1
            : 1;

        return $prefix . str_pad((string) $nextIncrement, 4, '0', STR_PAD_LEFT);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transaction_number')
                    ->default(fn() => static::generateTransactionNumber())
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated(), // tetap ikut tersimpan walau disabled
                Select::make('customer_id')
                    ->relationship(name: 'customer', titleAttribute: 'name')
                    ->searchable(['name', 'phone'])
                    ->placeholder('Search by phone or name')
                    ->hint("Select or Register a Customer to give Discount")
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('phone')
                            ->tel()
                            ->required(),
                        TextInput::make('address'),
                    ])
                    ->native(false)
                    ->live(), // <-- wajib, supaya perubahan customer_id memicu ulang kondisi disabled() di bawah

                Select::make('user_id')
                    ->relationship(name: 'user', titleAttribute: 'name')
                    ->searchable(['name', 'email'])
                    ->placeholder('Search by name or email')
                    ->native(false)
                    ->preload()
                    ->default(Auth::user()->id)
                    ->required(),
                // TextInput::make('discount_amount')
                //     ->disabled(fn (Get $get): bool => $get('customer_id') === null)
                //     ->dehydrated() // supaya value tetap ikut tersimpan walau field disabled
                //     ->required()
                //     ->numeric()
                //     ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                //     ->minValue(1)
                //     ->prefix('Rp')
                //     ->default(0),
                // TextInput::make('subtotal')
                //     ->required()
                //     ->numeric()
                //     ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                //     ->minValue(1)
                //     ->prefix('Rp')
                //     ->default(0),
                // TextInput::make('total_amount')
                //     ->required()
                //     ->numeric()
                //     ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                //     ->minValue(1)
                //     ->prefix('Rp')
                //     ->default(0),
                Select::make('payment_status')
                    ->native(false)
                    ->options([
                        'unpaid' => 'UNPAID',
                        'partial' => 'PARTIALLY PAID',
                        'paid' => 'PAID',
                    ])
                    ->required()
                    ->default('unpaid'),
                Select::make('delivery_status')
                    ->disabled(fn(string $operation): bool => $operation === 'create')
                    ->native(false)
                    ->options([
                        'in_progress' => 'IN PROGRESS',
                        'delivered' => 'DELIVERED',
                    ])
                    ->required()
                    ->default('in_progress'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                // DateTimePicker::make('delivered_at')
                //     ->native(false)
                //     ->default(now()),
            ]);
    }
}
