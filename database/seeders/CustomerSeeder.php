<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('customers')->insert([
            ['name' => 'Hendra Wijaya', 'phone' => '081211112222', 'address' => 'Jl. Melati No. 12, Jakarta', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maya Sari', 'phone' => '081233334444', 'address' => 'Jl. Kenanga No. 5, Jakarta', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fajar Nugroho', 'phone' => '081255556666', 'address' => 'Jl. Mawar No. 8, Jakarta', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rina Kusuma', 'phone' => '081277778888', 'address' => 'Jl. Anggrek No. 3, Jakarta', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
