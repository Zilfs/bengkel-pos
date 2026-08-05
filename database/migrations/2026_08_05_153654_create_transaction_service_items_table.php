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
        Schema::create('transaction_service_items', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();
 
            $table->foreignId('service_type_id')
                ->constrained('service_types')
                ->restrictOnDelete(); // master jasa di-soft-delete, bukan dihapus permanen
 
            $table->foreignId('mechanic_id')
                ->constrained('mechanics')
                ->restrictOnDelete();
 
            // snapshot — nilai ini permanen, tidak berubah walau master data berubah
            $table->string('service_name_snapshot');
            $table->decimal('price_snapshot', 12, 2);
            $table->decimal('commission_percentage_snapshot', 5, 2);
            $table->decimal('commission_amount_snapshot', 12, 2);
 
            $table->timestamps();
            $table->softDeletes(); // jasa dibatalkan saat pengerjaan -> soft delete, bukan hapus permanen
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_service_items');
    }
};
