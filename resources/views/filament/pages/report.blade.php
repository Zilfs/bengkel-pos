<x-filament-panels::page>
    <form>
        {{ $this->form }}
    </form>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:24px;">

        <div>
            <h3 style="font-weight:600;font-size:16px;margin-bottom:12px;">Rekap Komisi per Mekanik</h3>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #e5e7eb;text-align:left;">
                        <th style="padding:8px;">Mekanik</th>
                        <th style="padding:8px;">Jumlah Jasa</th>
                        <th style="padding:8px;text-align:right;">Total Komisi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $commissionRows = $this->getCommissionByMechanic(); @endphp

                    @forelse ($commissionRows as $row)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:8px;">{{ $row->mechanic?->name ?? '—' }}</td>
                            <td style="padding:8px;">{{ $row->total_jasa }}</td>
                            <td style="padding:8px;text-align:right;">Rp
                                {{ number_format($row->total_commission, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding:8px;color:#9ca3af;">Tidak ada data pada rentang tanggal
                                ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($commissionRows->isNotEmpty())
                    <tfoot>
                        <tr style="border-top:2px solid #e5e7eb;font-weight:600;">
                            <td style="padding:8px;" colspan="2">Total</td>
                            <td style="padding:8px;text-align:right;">Rp
                                {{ number_format($commissionRows->sum('total_commission'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div>
            <h3 style="font-weight:600;font-size:16px;margin-bottom:12px;">Rekap Uang Masuk per Metode Pembayaran</h3>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #e5e7eb;text-align:left;">
                        <th style="padding:8px;">Metode</th>
                        <th style="padding:8px;">Jumlah Transaksi</th>
                        <th style="padding:8px;text-align:right;">Total Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    @php $incomeRows = $this->getIncomeByPaymentMethod(); @endphp

                    @forelse ($incomeRows as $row)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:8px;text-transform:uppercase;">{{ $row->payment_method }}</td>
                            <td style="padding:8px;">{{ $row->total_transaksi }}</td>
                            <td style="padding:8px;text-align:right;">Rp
                                {{ number_format($row->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding:8px;color:#9ca3af;">Tidak ada data pada rentang tanggal
                                ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($incomeRows->isNotEmpty())
                    <tfoot>
                        <tr style="border-top:2px solid #e5e7eb;font-weight:600;">
                            <td style="padding:8px;" colspan="2">Total</td>
                            <td style="padding:8px;text-align:right;">Rp
                                {{ number_format($incomeRows->sum('total_amount'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

    </div>
</x-filament-panels::page>
