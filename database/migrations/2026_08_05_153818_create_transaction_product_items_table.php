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
        Schema::create('transaction_product_items', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();
 
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete(); // master sparepart di-soft-delete, bukan dihapus permanen
 
            // snapshot — nilai ini permanen, tidak berubah walau harga master berubah
            $table->string('product_name_snapshot');
            $table->decimal('price_snapshot', 12, 2);
            $table->integer('quantity');
            $table->decimal('subtotal_snapshot', 12, 2);
 
            $table->timestamps();
            $table->softDeletes(); // part dibatalkan saat pengerjaan -> soft delete + stok dikembalikan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_product_items');
    }
};
