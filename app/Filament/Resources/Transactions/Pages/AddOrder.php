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
    return OrderForm::configure($schema, $this->record)
        ->model($this->record)
        ->statePath('data');
}

public function create(): void
{
    $state = $this->form->getState();

    $this->form->model($this->record)->saveRelationships();

    // hitung ulang subtotal dari data yang baru tersimpan di DB (bukan dari state form,
    // supaya konsisten dengan pola yang sudah ada di method ini sebelumnya)
    $subtotal = $this->record->serviceItems()->sum('price_snapshot')
        + $this->record->productItems()->sum('subtotal_snapshot');

    $discount = (float) ($state['discount_amount'] ?? 0);

    $this->record->update([
        'subtotal' => $subtotal,
        'discount_amount' => $discount,
        'total_amount' => max($subtotal - $discount, 0),
    ]);

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
            ->mapWithKeys(fn ($item) => [(string) $item->getKey() => $item->attributesToArray()])
            ->toArray(),

        'productItems' => $this->record->productItems
            ->mapWithKeys(fn ($item) => [(string) $item->getKey() => $item->attributesToArray()])
            ->toArray(),

        'discount_amount' => $this->record->discount_amount,
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
