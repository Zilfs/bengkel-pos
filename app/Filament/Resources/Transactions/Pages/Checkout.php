<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\Schemas\CheckoutForm;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

class Checkout extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = TransactionResource::class;

    protected string $view = 'filament.resources.transactions.pages.checkout';

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
        return CheckoutForm::configure($schema, $this->record)
            ->model($this->record)
            ->statePath('data');
    }

    public function create(): void
    {
        $totalDibayar = collect($this->data['payments'] ?? [])
            ->sum(fn ($row) => (float) ($row['amount'] ?? 0));

        $totalTagihan = (float) $this->record->total_amount;

        if (round($totalDibayar, 2) !== round($totalTagihan, 2)) {
            Notification::make()
                ->title('Nominal pembayaran tidak sesuai')
                ->body('Total pembayaran harus sama persis dengan total tagihan transaksi.')
                ->danger()
                ->send();

            return;
        }

        $this->form->model($this->record)->saveRelationships();

        $this->record->update(['payment_status' => 'paid']);
        $this->record->refresh();
        $this->fillFormFromRecord();

        Notification::make()
            ->title('Pembayaran berhasil disimpan')
            ->success()
            ->send();
    }

    private function fillFormFromRecord(): void
    {
        $this->form->fill([
            'payments' => $this->record->payments
                ->mapWithKeys(fn ($item) => [(string) $item->getKey() => $item->attributesToArray()])
                ->toArray(),
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Simpan Pembayaran')
                ->submit('create'),
            Action::make('cancel')
                ->color('secondary')
                ->label('Kembali')
                ->url(fn () => TransactionResource::getUrl('view', ['record' => $this->record])),
        ];
    }
}