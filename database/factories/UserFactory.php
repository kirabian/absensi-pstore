<?php

namespace Database\Factories;

use App\Models\Division; // <-- TAMBAHKAN INI
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Password default untuk semua user
            'remember_token' => Str::random(10),

            // --- INI BAGIAN PENTING (YANG DIPERBAIKI) ---
            'role' => 'user_biasa', // <-- Tanda => sudah ada

            // Ambil ID divisi secara acak dari divisi yang sudah ada
            'division_id' => Division::inRandomOrder()->first()->id,

            // Buat kode QR unik
            'qr_code_value' => $this->faker->unique()->uuid(),
        ];
    }
}
