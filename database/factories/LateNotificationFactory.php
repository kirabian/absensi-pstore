<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LateNotificationFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::where('role', 'user_biasa')->inRandomOrder()->first()->id,
            'message' => 'Lagi macet di ' . $this->faker->streetName(),
            'is_active' => $this->faker->boolean(80), // 80% aktif
        ];
    }
}
