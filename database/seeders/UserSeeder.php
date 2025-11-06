<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // PENTING: Kita buat user dengan role spesifik dulu
        // agar seeder lain tidak error

        // 1 Admin (tidak punya divisi)
        User::factory()->create([
            'name' => 'Admin PStore',
            'email' => 'admin@pstore.com',
            'role' => 'admin',
            'division_id' => null,
        ]);

        // 2 Security (tidak punya divisi)
        User::factory(2)->create([
            'role' => 'security',
            'division_id' => null,
        ]);

        // 3 Audit (punya divisi acak)
        User::factory(3)->create([
            'role' => 'audit',
        ]);

        // 4 User Biasa (punya divisi acak)
        // Kita buat 4, jadi total ada 1+2+3+4 = 10 user
        User::factory(4)->create([
            'role' => 'user_biasa',
        ]);
    }
}
