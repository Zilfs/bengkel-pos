<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('service_types')->insert([
            ['name' => 'Ganti Oli', 'default_price' => 50000, 'default_commission_percentage' => 10.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tune Up', 'default_price' => 150000, 'default_commission_percentage' => 20.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Turun Mesin', 'default_price' => 500000, 'default_commission_percentage' => 25.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Servis Rem', 'default_price' => 75000, 'default_commission_percentage' => 15.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ganti Aki', 'default_price' => 100000, 'default_commission_percentage' => 12.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
