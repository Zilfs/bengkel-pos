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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();
 
            $table->enum('type', ['sale_out', 'return_in', 'restock_in', 'adjustment']);
            $table->integer('quantity');
 
            $table->foreignId('transaction_product_item_id')
                ->nullable() // kosong untuk restock manual / adjustment yang tidak terkait transaksi
                ->constrained('transaction_product_items')
                ->nullOnDelete();
 
            $table->text('notes')->nullable();
 
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();
 
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
