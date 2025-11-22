<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        // HANYA buat Super Admin saja
        User::create([
            'name' => 'Super Admin PStore',
            'login_id' => 'superadmin', // ID Login untuk masuk sistem
            'email' => 'superadmin@pstore.com', // Opsional, hanya untuk sosmed/kontak
            'password' => Hash::make('password'),
            'role' => 'admin',
            'branch_id' => null,
            'division_id' => null,
            'qr_code_value' => Str::uuid(),
            // Sosial Media (opsional)
            'whatsapp' => '6281234567890',
            'instagram' => 'pstore.official',
            'tiktok' => 'pstore.tiktok',
            'facebook' => 'pstore.facebook',
            'linkedin' => 'pstore.linkedin',
            'hire_date' => now(),
        ]);
    }
}