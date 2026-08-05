<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            ['name' => 'Oli Federal 1L', 'sku' => 'OLI-FED-1L', 'selling_price' => 65000, 'cost_price' => 50000, 'stock_quantity' => 50, 'unit' => 'liter', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kampas Rem Depan', 'sku' => 'KRD-001', 'selling_price' => 45000, 'cost_price' => 30000, 'stock_quantity' => 30, 'unit' => 'pcs', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kampas Rem Belakang', 'sku' => 'KRB-001', 'selling_price' => 40000, 'cost_price' => 27000, 'stock_quantity' => 30, 'unit' => 'pcs', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Busi NGK', 'sku' => 'BSN-NGK', 'selling_price' => 25000, 'cost_price' => 15000, 'stock_quantity' => 100, 'unit' => 'pcs', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aki GS Astra', 'sku' => 'AKI-GS', 'selling_price' => 350000, 'cost_price' => 280000, 'stock_quantity' => 15, 'unit' => 'pcs', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Filter Udara', 'sku' => 'FLT-UDR', 'selling_price' => 35000, 'cost_price' => 22000, 'stock_quantity' => 40, 'unit' => 'pcs', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rantai Motor', 'sku' => 'RTN-001', 'selling_price' => 120000, 'cost_price' => 90000, 'stock_quantity' => 20, 'unit' => 'set', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ban Luar IRC', 'sku' => 'BAN-IRC', 'selling_price' => 250000, 'cost_price' => 190000, 'stock_quantity' => 25, 'unit' => 'pcs', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
