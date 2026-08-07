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
        $totalDibayar = round(
            collect($this->data['payments'] ?? [])->sum(fn ($row) => (float) ($row['amount'] ?? 0)),
            2
        );

        $totalTagihan = round((float) $this->record->total_amount, 2);

        // overpayment tetap ditolak -- total seluruh pembayaran tidak boleh melebihi total tagihan
        if ($totalDibayar > $totalTagihan) {
            Notification::make()
                ->title('The payment amount is incorrect.')
                ->body('The total payment cannot exceed the total transaction amount.')
                ->danger()
                ->send();

            return;
        }

        $this->form->model($this->record)->saveRelationships();

        $status = match (true) {
            $totalDibayar <= 0 => 'unpaid',
            $totalDibayar < $totalTagihan => 'partial',
            default => 'paid', // $totalDibayar === $totalTagihan
        };

        $this->record->update(['payment_status' => $status]);
        $this->record->refresh();
        $this->fillFormFromRecord();

        Notification::make()
            ->title(match ($status) {
                'paid' => 'Payment Saved — Fully Paid',
                'partial' => 'Payment Saved — Partially Paid',
                default => 'Payment Saved',
            })
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
                ->label('Save Payment')
                ->submit('create')
                ->disabled(fn (): bool => $this->record->payment_status === 'paid'),
            Action::make('cancel')
                ->color('secondary')
                ->label('Back')
                ->url(fn () => TransactionResource::getUrl('view', ['record' => $this->record])),
        ];
    }
}