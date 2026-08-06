<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\Schemas\OrderForm;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

class AddOrder extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = TransactionResource::class;

    protected string $view = 'filament.resources.transactions.pages.add-order';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public Transaction $record;

    public function mount(Transaction $record): void
    {
        $this->record = $record;
        $this->fillFormFromRecord();
    }

    public function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema)
            ->model($this->record)
            ->statePath('data');
    }

    public function create(): void
    {
        $this->form->model($this->record)->saveRelationships();

        // hitung ulang subtotal dari data yang baru tersimpan di DB
        $this->record->update([
            'subtotal' => $this->record->serviceItems()->sum('price_snapshot')
                + $this->record->productItems()->sum('subtotal_snapshot'),
        ]);

        // muat ulang record dari DB (biar relasi tidak pakai cache lama),
        // lalu isi ulang form supaya original_state cocok dengan data yang baru disimpan
        $this->record->refresh();
        $this->fillFormFromRecord();

        Notification::make()
            ->title('Order Saved')
            ->success()
            ->send();
    }

    private function fillFormFromRecord(): void
    {
        $this->form->fill([
            'serviceItems' => $this->record->serviceItems
                ->mapWithKeys(fn($item) => [(string) $item->getKey() => $item->attributesToArray()])
                ->toArray(),

            'productItems' => $this->record->productItems
                ->mapWithKeys(fn($item) => [(string) $item->getKey() => $item->attributesToArray()])
                ->toArray(),
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Save Order')
                ->submit('create'),
            Action::make('cancel')
                ->color('secondary')
                ->label('Back')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
