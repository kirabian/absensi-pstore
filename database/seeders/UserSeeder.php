<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Buat 1 Super Admin (tidak punya cabang dan divisi)
        User::factory()->create([
            'name' => 'Super Admin PStore',
            'email' => 'superadmin@pstore.com',
            'role' => 'admin',
            'branch_id' => null,
            'division_id' => null,
        ]);
    }
}