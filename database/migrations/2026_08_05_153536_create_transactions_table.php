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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
 
            $table->foreignId('customer_id')
                ->nullable() // walk-in tanpa data pelanggan tetap bisa transaksi
                ->constrained('customers')
                ->nullOnDelete();
 
            $table->foreignId('user_id') // kasir yang input
                ->constrained('users')
                ->restrictOnDelete();
 
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
 
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->enum('delivery_status', ['in_progress', 'delivered'])->default('in_progress');
 
            $table->text('notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
