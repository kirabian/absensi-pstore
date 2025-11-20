<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Branch;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        // Untuk user biasa, tetap butuh branch dan division
        $branch = Branch::inRandomOrder()->first();
        $division = $branch ? Division::where('branch_id', $branch->id)->inRandomOrder()->first() : null;

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'role' => 'user_biasa',
            'branch_id' => $branch ? $branch->id : null,
            'division_id' => $division ? $division->id : null,
            'qr_code_value' => $this->faker->unique()->uuid(),
        ];
    }

    /**
     * State untuk Super Admin
     */
    public function superAdmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Super Admin PStore',
                'email' => 'superadmin@pstore.com',
                'role' => 'admin',
                'branch_id' => null,
                'division_id' => null,
            ];
        });
    }
}