<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Division;
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
            'password' => bcrypt('password'), // password
            'remember_token' => Str::random(10),

            // --- DEFAULTS BARU (Akan ditimpa oleh Seeder) ---
            'role' => 'user_biasa',
            'branch_id' => Branch::factory(), // Defaultnya, buat cabang baru

            // Logika untuk mengambil divisi yang ada di DALAM cabang yang sama
            'division_id' => function (array $attributes) {
                // Cari divisi acak yang branch_id-nya sama
                return Division::where('branch_id', $attributes['branch_id'])
                    ->inRandomOrder()
                    ->first()?->id; // ->id (jika ada)
            },
            'qr_code_value' => $this->faker->unique()->uuid(),
        ];
    }
}
