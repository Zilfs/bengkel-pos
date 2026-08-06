<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Product;
use App\Models\ServiceType;
use App\Models\TransactionProductItem;
use App\Models\TransactionServiceItem;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class OrderForm
{
    private static function badge(string $color, string $text): HtmlString
    {
        $colors = [
            'success' => ['bg' => '#dcfce7', 'text' => '#15803d'], // hijau — tersimpan
            'warning' => ['bg' => '#fef9c3', 'text' => '#a16207'], // kuning — belum disimpan
            'info' => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],    // biru — diubah, belum disimpan
        ];

        $style = sprintf(
            'display:inline-flex;align-items:center;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:500;background-color:%s;color:%s;',
            $colors[$color]['bg'],
            $colors[$color]['text']
        );

        return new HtmlString(
            '<span style="' . $style . '">' . $text . '</span>'
        );
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Service Type')
                            ->schema([
                                Repeater::make('serviceItems')
                                    ->relationship('serviceItems')
                                    ->itemLabel(function (array $state): HtmlString {
                                        if (empty($state['id'])) {
                                            return static::badge('warning', 'Unsaved');
                                        }

                                        $record = TransactionServiceItem::find($state['id']);

                                        if (! $record) {
                                            return static::badge('warning', 'Unsaved');
                                        }

                                        $current = [
                                            'service_type_id' => (string) ($state['service_type_id'] ?? ''),
                                            'mechanic_id' => (string) ($state['mechanic_id'] ?? ''),
                                        ];

                                        $original = [
                                            'service_type_id' => (string) $record->service_type_id,
                                            'mechanic_id' => (string) $record->mechanic_id,
                                        ];

                                        return $original !== $current
                                            ? static::badge('info', 'Changed, Unsaved')
                                            : static::badge('success', 'Saved');
                                    })
                                    ->schema([
                                        Select::make('service_type_id')
                                            ->label("Service Type")
                                            ->options(ServiceType::query()->pluck('name', 'id'))
                                            ->searchable()
                                            ->native(false)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                                $service = ServiceType::find($state);

                                                $set('service_name_snapshot', $service?->name);
                                                $set('price_snapshot', $service?->default_price ?? 0);
                                                $set('commission_percentage_snapshot', $service?->default_commission_percentage ?? 0);
                                            }),
                                        Select::make('mechanic_id')
                                            ->relationship('mechanic', 'name')
                                            ->preload()
                                            ->searchable()
                                            ->native(false)
                                            ->required()
                                            ->live(),
                                        TextInput::make('price_snapshot')
                                            ->label('Price')
                                            ->numeric()
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                            ->prefix('Rp')
                                            ->required()
                                            ->disabled()
                                            ->dehydrated(),
                                        TextInput::make('commission_percentage_snapshot')
                                            ->label('Comission (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->required()
                                            ->disabled()
                                            ->dehydrated(),
                                    ])
                                    ->columns(4)
                                    ->reorderable(false)
                                    ->addActionLabel('Add Service')
                                    ->defaultItems(0),
                            ]),

                        Tab::make('Product / Sparepart')
                            ->schema([
                                Repeater::make('productItems')
                                    ->relationship('productItems')
                                    ->itemLabel(function (array $state): HtmlString {
                                        if (empty($state['id'])) {
                                            return static::badge('warning', 'Unsaved');
                                        }

                                        $record = TransactionProductItem::find($state['id']);

                                        if (! $record) {
                                            return static::badge('warning', 'Unsaved');
                                        }

                                        $current = [
                                            'product_id' => (string) ($state['product_id'] ?? ''),
                                            'quantity' => (string) ($state['quantity'] ?? ''),
                                        ];

                                        $original = [
                                            'product_id' => (string) $record->product_id,
                                            'quantity' => (string) $record->quantity,
                                        ];

                                        return $original !== $current
                                            ? static::badge('info', 'Changed, Unsaved')
                                            : static::badge('success', 'Saved');
                                    })
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Sparepart')
                                            ->options(Product::query()->pluck('name', 'id'))
                                            ->searchable()
                                            ->native(false)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                $product = Product::find($state);

                                                $set('product_name_snapshot', $product?->name);
                                                $set('price_snapshot', $product?->selling_price ?? 0);
                                                $set('subtotal_snapshot', ($product?->selling_price ?? 0) * (int) ($get('quantity') ?? 1));
                                            }),
                                        Hidden::make('product_name_snapshot'),
                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $set('subtotal_snapshot', (float) $get('price_snapshot') * (int) $get('quantity'));
                                            }),
                                        TextInput::make('price_snapshot')
                                            ->label('Price per item')
                                            ->numeric()
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                            ->prefix('Rp')
                                            ->required()
                                            ->disabled()
                                            ->dehydrated(),
                                        TextInput::make('subtotal_snapshot')
                                            ->label('Subtotal')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required()
                                            ->disabled()
                                            ->dehydrated(),
                                    ])
                                    ->columns(4)
                                    ->reorderable(false)
                                    ->addActionLabel('Add Sparepart')
                                    ->defaultItems(0),
                            ]),
                    ]),
                    Placeholder::make('subtotal_display')
                ->label('Subtotal')
                ->live()
                ->content(function (Get $get): string {
                    $serviceTotal = collect($get('serviceItems') ?? [])
                        ->sum(fn ($item) => (float) ($item['price_snapshot'] ?? 0));

                    $productTotal = collect($get('productItems') ?? [])
                        ->sum(fn ($item) => (float) ($item['subtotal_snapshot'] ?? 0));

                    return Number::currency($serviceTotal + $productTotal, 'IDR', locale: 'id');
                }),

            TextInput::make('discount_amount')
                ->label('Diskon')
                ->numeric()
                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                ->minValue(0)
                ->prefix('Rp')
                ->default($transaction->discount_amount ?? 0)
                ->live(onBlur: true)
                ->rule(function (Get $get) {
                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                        $serviceTotal = collect($get('serviceItems') ?? [])
                            ->sum(fn ($item) => (float) ($item['price_snapshot'] ?? 0));
                        $productTotal = collect($get('productItems') ?? [])
                            ->sum(fn ($item) => (float) ($item['subtotal_snapshot'] ?? 0));
                        $subtotal = $serviceTotal + $productTotal;

                        if ((float) $value > $subtotal) {
                            $fail('Diskon tidak boleh lebih besar dari subtotal.');
                        }
                    };
                }),

            Placeholder::make('total_display')
                ->label('Total')
                ->live()
                ->content(function (Get $get): string {
                    $serviceTotal = collect($get('serviceItems') ?? [])
                        ->sum(fn ($item) => (float) ($item['price_snapshot'] ?? 0));
                    $productTotal = collect($get('productItems') ?? [])
                        ->sum(fn ($item) => (float) ($item['subtotal_snapshot'] ?? 0));
                    $subtotal = $serviceTotal + $productTotal;

                    $discount = (float) ($get('discount_amount') ?? 0);

                    return Number::currency(max($subtotal - $discount, 0), 'IDR', locale: 'id');
                }),
            ]);
    }
}
