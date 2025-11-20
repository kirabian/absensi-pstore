<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // HANYA buat Super Admin saja
        User::create([
            'name' => 'Super Admin PStore',
            'email' => 'superadmin@pstore.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'branch_id' => null,
            'division_id' => null,
            'qr_code_value' => \Illuminate\Support\Str::uuid(),
        ]);
    }
}