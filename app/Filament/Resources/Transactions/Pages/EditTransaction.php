<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addOrder')
                ->label('Manage Order')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary')
                ->url(fn () => TransactionResource::getUrl('add-order', ['record' => $this->getRecord()])),
            Action::make('checkout')
                ->label('Checkout')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary')
                ->url(fn () => TransactionResource::getUrl('checkout', ['record' => $this->getRecord()])),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
