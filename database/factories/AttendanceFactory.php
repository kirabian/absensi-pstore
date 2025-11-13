<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
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
            'check_in_time' => $this->faker->dateTimeThisMonth(),
            'status' => 'pending_verification', // Default status
            'photo_path' => $this->faker->imageUrl(640, 480, 'people'), // Tambahkan foto palsu
            'latitude' => $this->faker->latitude, // Tambahkan lokasi palsu
            'longitude' => $this->faker->longitude, // Tambahkan lokasi palsu
            'scanned_by_user_id' => null,
            'verified_by_user_id' => null,
        ];
    }

    /**
     * State untuk absensi yang di-scan oleh Security
     */
    public function scannedBySecurity()
    {
        return $this->state(function (array $attributes) {
            // Ambil security acak
            $security = User::where('role', 'security')->inRandomOrder()->first();

            return [
                'status' => 'verified',
                // 'scanned_by_user_id' akan diisi oleh factory utama
                // (kita perlu ambil branch_id dari user yg diabsen, bukan security)
                'scanned_by_user_id' => $security?->id,
            ];
        });
    }
}
