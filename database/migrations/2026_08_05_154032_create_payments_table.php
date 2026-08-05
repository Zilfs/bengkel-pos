<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();
 
            $table->enum('payment_method', ['cash', 'qris', 'transfer', 'debit']);
            $table->decimal('amount', 12, 2);
 
            // dipakai untuk rekap uang masuk per metode pembayaran, terpisah dari tanggal transaksi
            $table->timestamp('paid_at');
 
            $table->foreignId('received_by')
                ->constrained('users')
                ->restrictOnDelete();
 
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
