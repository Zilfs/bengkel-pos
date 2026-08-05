<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MechanicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mechanics')->insert([
            ['name' => 'Budi Santoso', 'phone' => '081234567801', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Agus Wijaya', 'phone' => '081234567802', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dedi Kurniawan', 'phone' => '081234567803', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rian Saputra', 'phone' => '081234567804', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
