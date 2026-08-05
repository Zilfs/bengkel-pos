<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Owner',
                'email' => 'owner@example.com',
                'role' => 'owner',
                'password' => encrypt('owner12345'), // di-hash, tidak pernah disimpan plain text
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kasir',
                'email' => 'kasir@example.com',
                'role' => 'kasir',
                'password' => encrypt('kasir12345'), // di-hash, tidak pernah disimpan plain text
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
