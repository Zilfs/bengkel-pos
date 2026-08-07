## Getting Started
### 1. Clone Repository
```bash
git clone git@github.com:Zilfs/bengkel-pos.git
```
### 2. Install Dependency
```bash
cd ./bengkel-pos
composer install
npm install
```
### 3. Setup .env file
- Copy file .env.example
- Sesuaikan key dan value pada beberapa emvironment variable, contoh:
```bash
APP_NAME="Bengkel POS"
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
DB_DATABASE=/database/database.sqlite
```
### 4. Generating App Key
```bash
php artisan key:generate
```
### 5. Setup DB
#### Membuat file database.sqlite
##### Linux / MacOS
```bash
touch ./database/database.sqlite
```
##### Windows
```bash
echo. > ./database/database.sqlite
```
#### Running Migration & Seeder
```bash
php artisan migrate
php artisan db:seed
```
### 6. Run Application
```bash
composer run dev
```
### 7. Access Dashboard
#### Sebagai Owner
- email: owner@example.com
- password: owner12345
#### Sebagai Kasir
- email: kasir@example.com
- password: kasir12345

## Testing

### Runnina a test
```bash
php artisan test
```
atau
```bash
php artisan test --filter=CommissionNotAffectedByDiscountTest
php artisan test --filter=StockMovementOnProductItemChangeTest
php artisan test --filter=CheckoutPaymentStatusTest
```

Test 1: CommissionNotAffectedByDiscountTest
Membuat satu transaksi dengan diskon besar (Rp50.000) dan satu baris jasa, lalu memastikan commission_amount_snapshot yang tersimpan tetap dihitung murni dari price_snapshot × commission_percentage_snapshot, tidak ikut terpotong oleh discount_amount transaksi.

Alasan dipilih: ini aturan bisnis eksplisit dari awal ("diskon murni tanggung jawab owner, bukan hak mekanik yang dipotong"), tapi implementasinya cuma "hidup" karena commission_amount_snapshot dihitung dari kolom yang benar. Sekali saja ada yang menyederhanakan rumus jadi berbasis total_amount (misalnya demi "konsistensi" dengan subtotal lain), aturan ini akan bocor tanpa ada yang sadar sampai owner komplain gaji mekanik salah.

Test 2: StockMovementOnProductItemChangeTest
Menambah sparepart qty 3 (cek stok berkurang 3), mengubah qty jadi 5 (cek stok cuma berkurang selisihnya 2, bukan direset), lalu membatalkan item itu (cek stok kembali penuh berdasarkan qty terakhir 5, bukan qty awal 3 saat dibuat).

Alasan dipilih: logic wasChanged()/adjustStock() di TransactionProductItem mudah untuk salah tepat di titik "qty terakhir dengan qty awal". Jika terdapat perubahan dan tidak sadar deleted event harus pakai $item->quantity (state saat itu), bukan getOriginal('quantity'), stoknya akan salah setiap kali ada kombinasi edit-lalu-batal dan bug tersebut baru dapat diketahui setelah stok fisik dengan sistem sudah selisih berbulan-bulan.

Test 3: CheckoutPaymentStatusTest
Membayar sebagian (Rp300rb dari Rp500rb, harus diterima jadi partial), lalu mencoba menambah pembayaran yang totalnya melebihi tagihan (harus ditolak, status tidak berubah), lalu melunasi pas sisanya (harus jadi paid).

Alasan dipilih: Titik ini merupakan bagian krusial dalam manajemen cashflow bengkel, memastikan proses pembayaran dapat berjalan dengan sesuai.
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
