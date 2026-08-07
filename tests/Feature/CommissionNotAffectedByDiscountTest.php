<?php

namespace Tests\Feature;

use App\Models\Mechanic;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\TransactionServiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionNotAffectedByDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_level_discount_does_not_reduce_mechanic_commission(): void
    {
        $user = User::factory()->create();
        $mechanic = Mechanic::create(['name' => 'Budi Santoso', 'phone' => '081234567801', 'is_active' => true]);
        $serviceType = ServiceType::create([
            'name' => 'Tune Up',
            'default_price' => 150000,
            'default_commission_percentage' => 20,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'transaction_number' => 'TRX-TEST-0001',
            'user_id' => $user->id,
            'subtotal' => 150000,
            'discount_amount' => 50000, // diskon besar sengaja dipilih, untuk memastikan tidak "bocor" ke komisi
            'total_amount' => 100000,
            'payment_status' => 'unpaid',
            'delivery_status' => 'in_progress',
        ]);

        $serviceItem = TransactionServiceItem::create([
            'transaction_id' => $transaction->id,
            'service_type_id' => $serviceType->id,
            'mechanic_id' => $mechanic->id,
            'service_name_snapshot' => $serviceType->name,
            'price_snapshot' => $serviceType->default_price,
            'commission_percentage_snapshot' => $serviceType->default_commission_percentage,
        ]);

        // komisi = price_snapshot x commission_percentage / 100 = 150000 x 20% = 30000
        // Nilai ini TIDAK BOLEH terpengaruh oleh discount_amount di level transaksi (100000 vs 150000).
        $this->assertEquals(30000, $serviceItem->fresh()->commission_amount_snapshot);
    }
}