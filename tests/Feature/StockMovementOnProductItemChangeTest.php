<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionProductItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementOnProductItemChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_adjusts_correctly_across_add_update_and_cancel(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::create([
            'name' => 'Oli Federal 1L',
            'selling_price' => 65000,
            'stock_quantity' => 10,
            'unit' => 'liter',
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'transaction_number' => 'TRX-TEST-0002',
            'user_id' => $user->id,
            'subtotal' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'payment_status' => 'unpaid',
            'delivery_status' => 'in_progress',
        ]);

        // tambah 3 -> stok berkurang 3 (10 -> 7)
        $item = TransactionProductItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertEquals(7, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'sale_out',
            'quantity' => 3,
        ]);

        // qty diubah dari 3 jadi 5 -> stok cuma berkurang SELISIHnya (2), bukan direset total (7 -> 5)
        $item->update(['quantity' => 5]);
        $this->assertEquals(5, $product->fresh()->stock_quantity);

        // dibatalkan (soft delete) -> stok dikembalikan PENUH sesuai qty TERAKHIR (5), bukan qty awal saat dibuat (3)
        $item->delete();
        $this->assertEquals(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'return_in',
            'quantity' => 5,
        ]);

        // pastikan baris item-nya sendiri soft-deleted, bukan hilang permanen dari database
        $this->assertSoftDeleted('transaction_product_items', ['id' => $item->id]);
    }
}