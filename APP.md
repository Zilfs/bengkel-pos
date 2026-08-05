# Kloa POS — Dokumentasi Aplikasi

## 1. Konteks

Kloa POS adalah aplikasi kasir untuk UMKM Indonesia. Dokumen ini disusun berdasarkan cara operasional salah satu merchant contoh, **Bengkel Jaya Motor** (bengkel servis motor).

**Tech stack:** Laravel + Filament (admin panel/backend), SQLite (database).

**Karakteristik bisnis yang membentuk desain aplikasi:**

- Satu nota bisa berisi jasa servis dan sparepart sekaligus.
- Mekanik dapat komisi dari jasa, bukan dari sparepart, dan persentasenya beda per jenis jasa serta bisa diubah owner kapan saja.
- Satu nota bisa dikerjakan beberapa mekanik berbeda untuk tiap baris jasa.
- Diskon bisa diberikan di level transaksi (bukan per item), dan diskon ini tidak memotong komisi mekanik.
- Pembayaran bisa split (lebih dari satu metode dalam satu transaksi) dan bisa dicicil (DP di awal, pelunasan di hari lain).
- Tidak ada payment gateway — kasir konfirmasi pembayaran secara manual di aplikasi.
- Ada sistem membership sederhana berbasis nomor HP pelanggan.
- Perubahan harga master (jasa/sparepart) atau persentase komisi tidak boleh mengubah nilai transaksi yang sudah terjadi sebelumnya (snapshot).
- Stok sparepart harus otomatis berkurang saat item masuk nota, dan otomatis kembali kalau item itu dibatalkan saat pengerjaan berjalan.

## 2. Ringkasan Skema Database

**Master data**

- `mechanics` — data mekanik (soft delete agar tetap valid sebagai referensi histori)
- `customers` — data pelanggan, kunci membership via nomor HP
- `service_types` — jenis jasa beserta harga dan persentase komisi default
- `products` — sparepart beserta harga jual dan stok
- `users` — akun kasir/owner

**Transaksi**

- `transactions` — header nota: pelanggan, kasir, diskon, subtotal, total, `payment_status` (unpaid/partial/paid), `delivery_status` (in_progress/delivered)
- `transaction_service_items` — baris jasa per nota: mekanik penanggung jawab, **snapshot** harga & persentase komisi & nominal komisi saat itu
- `transaction_product_items` — baris sparepart per nota: quantity, **snapshot** harga saat itu
- `payments` — satu baris per event pembayaran (metode, nominal, tanggal bayar); banyak baris per transaksi menangani split payment maupun DP+pelunasan
- `stock_movements` — jejak audit keluar/masuknya stok (terpakai di transaksi, dikembalikan karena batal, atau restock manual)

File skema lengkap (DBML, siap paste ke dbdiagram.io): `kloa-pos-schema.dbml`.

## 3. Alur Aplikasi

### 3.1 Membuka transaksi baru

Kasir membuat `transactions` baru, opsional mengaitkan `customer_id` (kalau pelanggan baru, dicatat dulu by nomor HP; kalau walk-in tanpa data, `customer_id` dibiarkan kosong). Status awal: `payment_status = unpaid`, `delivery_status = in_progress`.

### 3.2 Menambahkan jasa dan sparepart

Untuk tiap jasa yang ditambahkan, sistem mengambil harga dan persentase komisi terkini dari `service_types` lalu menyimpannya sebagai snapshot di `transaction_service_items`, sekaligus mencatat mekanik yang mengerjakan jasa tersebut. Tiap baris jasa bisa punya mekanik berbeda.

Untuk tiap sparepart yang ditambahkan, sistem mengambil harga terkini dari `products`, menyimpan snapshot di `transaction_product_items`, lalu langsung mengurangi `products.stock_quantity` dan mencatat `stock_movements` bertipe `sale_out`.

Selama `payment_status` belum `paid` **atau** `delivery_status` belum `delivered`, kasir masih boleh menambah atau membatalkan baris jasa/sparepart — ini mengakomodasi kasus penemuan kerusakan tambahan di tengah pengerjaan. Kalau sebuah baris sparepart dibatalkan, baris itu di-soft-delete dan sistem otomatis mencatat `stock_movements` bertipe `return_in` untuk mengembalikan stok.

### 3.3 Menghitung total

`subtotal` = jumlah seluruh `price_snapshot` (jasa + sparepart yang masih aktif). `total_amount` = `subtotal` dikurangi `discount_amount` (kalau owner memberi potongan langganan). Diskon ini murni mengurangi pendapatan owner dan **tidak** menyentuh `commission_amount_snapshot` di baris jasa manapun.

### 3.4 Pembayaran

Kasir mencatat pembayaran secara manual ke tabel `payments`, satu baris per event bayar. Skenario yang sama-sama ditangani tabel ini:

- **Split payment**: dua atau lebih baris `payments` dengan `paid_at` yang sama tapi `payment_method` berbeda (mis. Rp100.000 cash + Rp140.000 QRIS) untuk satu `transaction_id`.
- **DP dan pelunasan**: satu baris `payments` saat DP diberikan, satu baris lagi di hari lain saat pelunasan, `paid_at` berbeda.

Setiap kali ada baris `payments` baru (atau dihapus), sistem membandingkan `SUM(payments.amount)` dengan `transactions.total_amount` untuk memperbarui `payment_status` menjadi `unpaid`, `partial`, atau `paid`.

### 3.5 Penyerahan motor

Saat motor diambil pelanggan, kasir menandai `delivery_status = delivered` dan mengisi `delivered_at`. Begitu transaksi berstatus `paid` **dan** `delivered` sekaligus, transaksi dianggap final dan tidak lagi bisa direvisi.

### 3.6 Laporan akhir bulan

Ketiga laporan yang dibutuhkan owner diambil dari sumber tanggal yang berbeda, sesuai sifat datanya:

- **Rekap komisi per mekanik** — `SUM(commission_amount_snapshot)` dari `transaction_service_items`, dikelompokkan per `mechanic_id`, difilter berdasarkan tanggal transaksi (kapan jasa dikerjakan). Baris yang sudah di-soft-delete (jasa dibatalkan) otomatis tidak ikut terhitung.
- **Rekap uang masuk per metode pembayaran** — `SUM(amount)` dari `payments`, dikelompokkan per `payment_method`, difilter berdasarkan `paid_at` (bukan tanggal transaksi). Ini penting untuk kasus DP: uang yang masuk bulan ini harus tercatat di bulan ini juga untuk pencocokan mutasi rekening, walau transaksinya dibuat di bulan sebelumnya.
- **Daftar seluruh transaksi dalam rentang tanggal** — query langsung ke `transactions`, difilter berdasarkan `created_at`.
