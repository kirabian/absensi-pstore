<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LateNotificationFactory extends Factory
{
    public function definition()
    {
        // --- LOGIKA BARU ---
        // 1. Ambil satu user 'user_biasa' secara acak
        $user = User::where('role', 'user_biasa')->inRandomOrder()->first();

        // 2. Jika tidak ada user, buat satu
        if (!$user) {
            $user = User::factory()->create(['role' => 'user_biasa']);
        }
        // --- BATAS LOGIKA BARU ---

        return [
            'user_id' => $user->id,
            'branch_id' => $user->branch_id, // <-- PERBAIKAN UTAMA ADA DI SINI
            'message' => 'Lagi macet di ' . $this->faker->streetName(),
            'is_active' => true,
        ];
    }
}
