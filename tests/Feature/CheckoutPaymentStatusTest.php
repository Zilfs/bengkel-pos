<?php

namespace Tests\Feature;

use App\Filament\Resources\Transactions\Pages\Checkout;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutPaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_payment_is_accepted_and_overpayment_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $transaction = Transaction::create([
            'transaction_number' => 'TRX-TEST-0003',
            'user_id' => $user->id,
            'subtotal' => 500000,
            'discount_amount' => 0,
            'total_amount' => 500000,
            'payment_status' => 'unpaid',
            'delivery_status' => 'in_progress',
        ]);

        // bayar 300.000 dari total 500.000 -> harus DITERIMA, status jadi 'partial'
        Livewire::test(Checkout::class, ['record' => $transaction])
            ->set('data.payments', [
                'new_1' => [
                    'payment_method' => 'cash',
                    'amount' => 300000,
                    'paid_at' => now()->toDateTimeString(),
                    'received_by' => $user->id,
                ],
            ])
            ->call('create');

        $this->assertEquals('partial', $transaction->fresh()->payment_status);
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $transaction->id,
            'amount' => 300000,
        ]);

        // coba tambah lagi 300.000 (total jadi 600.000, melebihi tagihan 500.000) -> harus DITOLAK
        Livewire::test(Checkout::class, ['record' => $transaction->fresh()])
            ->set('data.payments', [
                'existing_1' => [
                    'payment_method' => 'cash',
                    'amount' => 300000,
                    'paid_at' => now()->toDateTimeString(),
                    'received_by' => $user->id,
                ],
                'new_2' => [
                    'payment_method' => 'qris',
                    'amount' => 300000,
                    'paid_at' => now()->toDateTimeString(),
                    'received_by' => $user->id,
                ],
            ])
            ->call('create');

        // status TIDAK berubah dari 'partial' -- pembayaran kedua ini seharusnya tidak pernah tersimpan
        $this->assertEquals('partial', $transaction->fresh()->payment_status);
        $this->assertDatabaseMissing('payments', [
            'transaction_id' => $transaction->id,
            'amount' => 300000,
            'payment_method' => 'qris',
        ]);

        // lunasi dengan pas 200.000 sisanya -> harus DITERIMA, status jadi 'paid'
        Livewire::test(Checkout::class, ['record' => $transaction->fresh()])
            ->set('data.payments', [
                'existing_1' => [
                    'payment_method' => 'cash',
                    'amount' => 300000,
                    'paid_at' => now()->toDateTimeString(),
                    'received_by' => $user->id,
                ],
                'new_2' => [
                    'payment_method' => 'transfer',
                    'amount' => 200000,
                    'paid_at' => now()->toDateTimeString(),
                    'received_by' => $user->id,
                ],
            ])
            ->call('create');

        $this->assertEquals('paid', $transaction->fresh()->payment_status);
    }
}