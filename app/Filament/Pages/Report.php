<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use App\Models\TransactionServiceItem;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Report extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Report';
    protected static ?int $navigationSort = 8;
    protected static ?string $title = 'Commission and Payment Report';

    protected string $view = 'filament.pages.report';

    public ?array $data = [];
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->role == 'owner';
    }
    public static function canAccess(): bool
    {
        return Auth::user()->role == 'owner';
    }
    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date_from')
                    ->label('Date From')
                    ->native(false)
                    ->required()
                    ->live(),

                DatePicker::make('date_to')
                    ->label('Date To')
                    ->native(false)
                    ->required()
                    ->live(),
            ])
            ->statePath('data')
            ->columns(2);
    }

    // Difilter berdasarkan tanggal TRANSAKSI (kapan jasa dikerjakan),
    // sesuai keputusan desain di APP.md. Baris jasa yang dibatalkan (soft-deleted)
    // otomatis tidak ikut terhitung karena TransactionServiceItem pakai SoftDeletes.
    public function getCommissionByMechanic(): Collection
    {
        [$from, $to] = $this->getDateRange();

        return TransactionServiceItem::query()
            ->whereHas('transaction', function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to]);
            })
            ->selectRaw('mechanic_id, SUM(commission_amount_snapshot) as total_commission, COUNT(*) as total_jasa')
            ->groupBy('mechanic_id')
            ->with('mechanic')
            ->get();
    }

    // Difilter berdasarkan tanggal BAYAR (paid_at), bukan tanggal transaksi,
    // supaya kasus DP tercatat di bulan uangnya benar-benar masuk kas/rekening.
    public function getIncomeByPaymentMethod(): Collection
    {
        [$from, $to] = $this->getDateRange();

        return Payment::query()
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('payment_method, SUM(amount) as total_amount, COUNT(*) as total_transaksi')
            ->groupBy('payment_method')
            ->get();
    }

    private function getDateRange(): array
    {
        $from = Carbon::parse($this->data['date_from'] ?? now()->startOfMonth())->startOfDay();
        $to = Carbon::parse($this->data['date_to'] ?? now()->endOfMonth())->endOfDay();

        return [$from, $to];
    }
}