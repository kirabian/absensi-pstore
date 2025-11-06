<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        return [
            // Ambil ID user yang rolenya 'user_biasa' secara acak
            'user_id' => User::where('role', 'user_biasa')->inRandomOrder()->first()->id,
            'check_in_time' => $this->faker->dateTimeThisMonth(),
            'status' => 'pending_verification', // Default status
            'photo_path' => null,
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
            return [
                'status' => 'verified',
                'photo_path' => $this->faker->imageUrl(640, 480, 'people'),
                'scanned_by_user_id' => User::where('role', 'security')->inRandomOrder()->first()->id,
            ];
        });
    }
}
