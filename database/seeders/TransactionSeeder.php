<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    private array $mechanics;

    private array $services; // ['name' => ['id'=>, 'price'=>, 'commission'=>]]

    private array $products; // ['name' => ['id'=>, 'price'=>]]

    private array $customers;

    private array $users;

    public function run(): void
    {
        $this->mechanics = DB::table('mechanics')->pluck('id', 'name')->toArray();
        $this->customers = DB::table('customers')->pluck('id', 'name')->toArray();
        $this->users = DB::table('users')->pluck('id', 'name')->toArray();

        $this->services = DB::table('service_types')->get()
            ->keyBy('name')
            ->map(fn ($s) => ['id' => $s->id, 'price' => $s->default_price, 'commission' => $s->default_commission_percentage])
            ->toArray();

        $this->products = DB::table('products')->get()
            ->keyBy('name')
            ->map(fn ($p) => ['id' => $p->id, 'price' => $p->selling_price])
            ->toArray();

        $this->transaksi1();
        $this->transaksi2();
        $this->transaksi3();
        $this->transaksi4();
        $this->transaksi5();
        $this->transaksi6();
        $this->transaksi7();
        $this->transaksi8();
        $this->transaksi9();
        $this->transaksi10();
    }

    // 1) Transaksi sederhana: 1 jasa + 1 sparepart, 1 metode bayar, tanpa diskon
    private function transaksi1(): void
    {
        $tanggal = Carbon::parse('2026-07-10 09:00:00');
        $trxId = $this->buatTransaksi('TRX-20260710-0001', 'Hendra Wijaya', 'Kasir', $tanggal);

        $this->tambahJasa($trxId, 'Ganti Oli', 'Budi Santoso', $tanggal);
        $this->tambahSparepart($trxId, 'Oli Federal 1L', 1, $tanggal);

        $this->tutupTransaksi($trxId);
        $this->tambahBayar($trxId, 'cash', $this->totalAktif($trxId), $tanggal, 'Kasir');
        $this->selesaikanPengiriman($trxId, $tanggal);
    }

    // 2) Dua jasa dengan DUA mekanik berbeda + split payment (cash + transfer)
    private function transaksi2(): void
    {
        $tanggal = Carbon::parse('2026-07-12 10:30:00');
        $trxId = $this->buatTransaksi('TRX-20260712-0002', 'Maya Sari', 'Kasir', $tanggal);

        $this->tambahJasa($trxId, 'Tune Up', 'Agus Wijaya', $tanggal);
        $this->tambahJasa($trxId, 'Ganti Oli', 'Dedi Kurniawan', $tanggal);
        $this->tambahSparepart($trxId, 'Oli Federal 1L', 1, $tanggal);
        $this->tambahSparepart($trxId, 'Filter Udara', 1, $tanggal);

        $this->tutupTransaksi($trxId);
        $this->tambahBayar($trxId, 'cash', 150000, $tanggal, 'Kasir');
        $this->tambahBayar($trxId, 'transfer', 150000, $tanggal, 'Owner');
        $this->selesaikanPengiriman($trxId, $tanggal);
    }

    // 3) Walk-in (tanpa data pelanggan), bayar QRIS penuh
    private function transaksi3(): void
    {
        $tanggal = Carbon::parse('2026-07-15 13:00:00');
        $trxId = $this->buatTransaksi('TRX-20260715-0003', null, 'Kasir', $tanggal);

        $this->tambahJasa($trxId, 'Servis Rem', 'Rian Saputra', $tanggal);
        $this->tambahSparepart($trxId, 'Kampas Rem Depan', 1, $tanggal);
        $this->tambahSparepart($trxId, 'Kampas Rem Belakang', 1, $tanggal);

        $this->tutupTransaksi($trxId);
        $this->tambahBayar($trxId, 'qris', $this->totalAktif($trxId), $tanggal, 'Kasir');
        $this->selesaikanPengiriman($trxId, $tanggal);
    }

    // 4) Transaksi dengan DISKON (pelanggan langganan) — diskon tidak memotong komisi mekanik
    private function transaksi4(): void
    {
        $tanggal = Carbon::parse('2026-07-18 11:00:00');
        $trxId = $this->buatTransaksi('TRX-20260718-0004', 'Hendra Wijaya', 'Owner', $tanggal);

        $this->tambahJasa($trxId, 'Ganti Oli', 'Budi Santoso', $tanggal);
        $this->tambahJasa($trxId, 'Ganti Aki', 'Agus Wijaya', $tanggal);
        $this->tambahSparepart($trxId, 'Aki GS Astra', 1, $tanggal);

        $this->tutupTransaksi($trxId, diskon: 25000);
        $this->tambahBayar($trxId, 'transfer', $this->totalAktif($trxId), $tanggal, 'Owner');
        $this->selesaikanPengiriman($trxId, $tanggal);
    }

    // 5) Pekerjaan besar: DP di awal, pelunasan beberapa hari kemudian (payment_status -> paid)
    private function transaksi5(): void
    {
        $tanggal = Carbon::parse('2026-07-20 08:30:00');
        $trxId = $this->buatTransaksi('TRX-20260720-0005', 'Fajar Nugroho', 'Kasir', $tanggal);

        $this->tambahJasa($trxId, 'Turun Mesin', 'Dedi Kurniawan', $tanggal);
        $this->tambahSparepart($trxId, 'Rantai Motor', 1, $tanggal);

        $this->tutupTransaksi($trxId);
        $this->tambahBayar($trxId, 'cash', 300000, $tanggal, 'Kasir'); // DP
        $this->tambahBayar($trxId, 'qris', 320000, Carbon::parse('2026-07-25 16:00:00'), 'Kasir'); // pelunasan
        $this->selesaikanPengiriman($trxId, Carbon::parse('2026-07-25 16:30:00'));
    }

    // 6) TIGA mekanik berbeda dalam satu transaksi + split payment (cash + debit)
    private function transaksi6(): void
    {
        $tanggal = Carbon::parse('2026-07-22 14:00:00');
        $trxId = $this->buatTransaksi('TRX-20260722-0006', 'Rina Kusuma', 'Owner', $tanggal);

        $this->tambahJasa($trxId, 'Ganti Oli', 'Budi Santoso', $tanggal);
        $this->tambahJasa($trxId, 'Servis Rem', 'Agus Wijaya', $tanggal);
        $this->tambahJasa($trxId, 'Ganti Aki', 'Rian Saputra', $tanggal);
        $this->tambahSparepart($trxId, 'Busi NGK', 2, $tanggal);

        $this->tutupTransaksi($trxId);
        $this->tambahBayar($trxId, 'cash', 100000, $tanggal, 'Owner');
        $this->tambahBayar($trxId, 'debit', 175000, $tanggal, 'Owner');
        $this->selesaikanPengiriman($trxId, $tanggal);
    }

    // 7) Sparepart saja, tanpa jasa sama sekali, walk-in
    private function transaksi7(): void
    {
        $tanggal = Carbon::parse('2026-07-25 15:30:00');
        $trxId = $this->buatTransaksi('TRX-20260725-0007', null, 'Kasir', $tanggal);

        $this->tambahSparepart($trxId, 'Busi NGK', 4, $tanggal);
        $this->tambahSparepart($trxId, 'Filter Udara', 1, $tanggal);

        $this->tutupTransaksi($trxId);
        $this->tambahBayar($trxId, 'cash', $this->totalAktif($trxId), $tanggal, 'Kasir');
        $this->selesaikanPengiriman($trxId, $tanggal);
    }

    // 8) Kasus koreksi: sparepart sempat ditambahkan lalu DIBATALKAN saat pengerjaan
    //    -> baris di-soft-delete, stok keluar lalu otomatis dikembalikan (stock_movements)
    private function transaksi8(): void
    {
        $tanggal = Carbon::parse('2026-07-28 09:00:00');
        $trxId = $this->buatTransaksi('TRX-20260728-0008', 'Maya Sari', 'Kasir', $tanggal);

        $this->tambahJasa($trxId, 'Ganti Oli', 'Budi Santoso', $tanggal);
        $this->tambahSparepart($trxId, 'Oli Federal 1L', 1, $tanggal);

        // pelanggan sempat minta ganti ban, tapi batal di tengah pengerjaan
        $itemBanId = $this->tambahSparepart($trxId, 'Ban Luar IRC', 1, $tanggal);
        $this->batalkanSparepart($itemBanId, $tanggal->copy()->addHours(2), 'Kasir');

        $this->tutupTransaksi($trxId); // subtotal dihitung ulang, hanya baris yang masih aktif
        $this->tambahBayar($trxId, 'cash', $this->totalAktif($trxId), $tanggal, 'Kasir');
        $this->selesaikanPengiriman($trxId, $tanggal->copy()->addHours(3));
    }

    // 9) Diskon + dua mekanik + TIGA metode pembayaran sekaligus dalam satu transaksi
    private function transaksi9(): void
    {
        $tanggal = Carbon::parse('2026-08-01 10:00:00');
        $trxId = $this->buatTransaksi('TRX-20260801-0009', 'Hendra Wijaya', 'Owner', $tanggal);

        $this->tambahJasa($trxId, 'Tune Up', 'Agus Wijaya', $tanggal);
        $this->tambahJasa($trxId, 'Turun Mesin', 'Dedi Kurniawan', $tanggal);
        $this->tambahSparepart($trxId, 'Kampas Rem Depan', 2, $tanggal);
        $this->tambahSparepart($trxId, 'Rantai Motor', 1, $tanggal);

        $this->tutupTransaksi($trxId, diskon: 50000);
        $this->tambahBayar($trxId, 'cash', 300000, $tanggal, 'Owner');
        $this->tambahBayar($trxId, 'transfer', 300000, $tanggal, 'Owner');
        $this->tambahBayar($trxId, 'qris', 210000, $tanggal, 'Owner');
        $this->selesaikanPengiriman($trxId, $tanggal);
    }

    // 10) Pekerjaan besar yang MASIH BERJALAN: baru DP, motor belum diambil
    //     (payment_status = partial, delivery_status = in_progress -> masih boleh direvisi)
    private function transaksi10(): void
    {
        $tanggal = Carbon::parse('2026-08-03 09:00:00');
        $trxId = $this->buatTransaksi('TRX-20260803-0010', 'Rina Kusuma', 'Kasir', $tanggal);

        $this->tambahJasa($trxId, 'Turun Mesin', 'Rian Saputra', $tanggal);
        $this->tambahSparepart($trxId, 'Aki GS Astra', 1, $tanggal);

        $this->tutupTransaksi($trxId);
        $this->tambahBayar($trxId, 'cash', 400000, $tanggal, 'Kasir'); // baru DP, belum lunas
        // sengaja tidak dipanggil selesaikanPengiriman() -> delivery_status tetap in_progress
    }

    // ================= Helper ================= //

    private function buatTransaksi(string $nomor, ?string $namaCustomer, string $namaKasir, Carbon $tanggal): int
    {
        return DB::table('transactions')->insertGetId([
            'transaction_number' => $nomor,
            'customer_id' => $namaCustomer ? $this->customers[$namaCustomer] : null,
            'user_id' => $this->users[$namaKasir],
            'discount_amount' => 0,
            'subtotal' => 0,
            'total_amount' => 0,
            'payment_status' => 'unpaid',
            'delivery_status' => 'in_progress',
            'created_at' => $tanggal,
            'updated_at' => $tanggal,
        ]);
    }

    private function tambahJasa(int $trxId, string $namaJasa, string $namaMekanik, Carbon $tanggal): int
    {
        $jasa = $this->services[$namaJasa];
        $komisi = round($jasa['price'] * $jasa['commission'] / 100, 2);

        return DB::table('transaction_service_items')->insertGetId([
            'transaction_id' => $trxId,
            'service_type_id' => $jasa['id'],
            'mechanic_id' => $this->mechanics[$namaMekanik],
            'service_name_snapshot' => $namaJasa,
            'price_snapshot' => $jasa['price'],
            'commission_percentage_snapshot' => $jasa['commission'],
            'commission_amount_snapshot' => $komisi,
            'created_at' => $tanggal,
            'updated_at' => $tanggal,
        ]);
    }

    private function tambahSparepart(int $trxId, string $namaProduk, int $qty, Carbon $tanggal): int
    {
        $produk = $this->products[$namaProduk];
        $subtotal = $produk['price'] * $qty;

        $itemId = DB::table('transaction_product_items')->insertGetId([
            'transaction_id' => $trxId,
            'product_id' => $produk['id'],
            'product_name_snapshot' => $namaProduk,
            'price_snapshot' => $produk['price'],
            'quantity' => $qty,
            'subtotal_snapshot' => $subtotal,
            'created_at' => $tanggal,
            'updated_at' => $tanggal,
        ]);

        DB::table('products')->where('id', $produk['id'])->decrement('stock_quantity', $qty);

        DB::table('stock_movements')->insert([
            'product_id' => $produk['id'],
            'type' => 'sale_out',
            'quantity' => $qty,
            'transaction_product_item_id' => $itemId,
            'notes' => "Terpakai di transaksi #{$trxId}",
            'created_by' => $this->users['Kasir'],
            'created_at' => $tanggal,
        ]);

        return $itemId;
    }

    // Membatalkan sparepart yang sudah terlanjur masuk nota: soft delete + stok dikembalikan
    private function batalkanSparepart(int $itemId, Carbon $tanggal, string $namaKasir): void
    {
        $item = DB::table('transaction_product_items')->where('id', $itemId)->first();

        DB::table('transaction_product_items')->where('id', $itemId)->update([
            'deleted_at' => $tanggal,
            'updated_at' => $tanggal,
        ]);

        DB::table('products')->where('id', $item->product_id)->increment('stock_quantity', $item->quantity);

        DB::table('stock_movements')->insert([
            'product_id' => $item->product_id,
            'type' => 'return_in',
            'quantity' => $item->quantity,
            'transaction_product_item_id' => $itemId,
            'notes' => 'Dibatalkan pelanggan saat pengerjaan berlangsung',
            'created_by' => $this->users[$namaKasir],
            'created_at' => $tanggal,
        ]);
    }

    // Menghitung ulang subtotal & total dari baris yang MASIH AKTIF (belum soft-deleted), lalu simpan
    private function tutupTransaksi(int $trxId, float $diskon = 0): void
    {
        $totalJasa = DB::table('transaction_service_items')
            ->where('transaction_id', $trxId)
            ->whereNull('deleted_at')
            ->sum('price_snapshot');

        $totalProduk = DB::table('transaction_product_items')
            ->where('transaction_id', $trxId)
            ->whereNull('deleted_at')
            ->sum('subtotal_snapshot');

        $subtotal = $totalJasa + $totalProduk;

        DB::table('transactions')->where('id', $trxId)->update([
            'subtotal' => $subtotal,
            'discount_amount' => $diskon,
            'total_amount' => $subtotal - $diskon,
        ]);
    }

    private function totalAktif(int $trxId): float
    {
        return DB::table('transactions')->where('id', $trxId)->value('total_amount');
    }

    private function tambahBayar(int $trxId, string $metode, float $jumlah, Carbon $tanggal, string $namaKasir): void
    {
        DB::table('payments')->insert([
            'transaction_id' => $trxId,
            'payment_method' => $metode,
            'amount' => $jumlah,
            'paid_at' => $tanggal,
            'received_by' => $this->users[$namaKasir],
            'created_at' => $tanggal,
        ]);

        $totalDibayar = DB::table('payments')->where('transaction_id', $trxId)->sum('amount');
        $totalTagihan = $this->totalAktif($trxId);

        $status = 'unpaid';
        if ($totalDibayar >= $totalTagihan && $totalTagihan > 0) {
            $status = 'paid';
        } elseif ($totalDibayar > 0) {
            $status = 'partial';
        }

        DB::table('transactions')->where('id', $trxId)->update(['payment_status' => $status]);
    }

    private function selesaikanPengiriman(int $trxId, Carbon $tanggal): void
    {
        DB::table('transactions')->where('id', $trxId)->update([
            'delivery_status' => 'delivered',
            'delivered_at' => $tanggal,
            'updated_at' => $tanggal,
        ]);
    }
}
