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
        // Ambil branch random yang ada
        $branch = Branch::inRandomOrder()->first();
        
        // Ambil division random
        $division = Division::inRandomOrder()->first();

        return [
            'name' => $this->faker->name(),
            'login_id' => $this->faker->unique()->userName(), // ID Login wajib dan unique
            'email' => $this->faker->optional()->safeEmail(), // Email opsional
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Password default
            'remember_token' => Str::random(10),
            'role' => 'user_biasa',
            'branch_id' => $branch ? $branch->id : null,
            'division_id' => $division ? $division->id : null,
            'qr_code_value' => Str::uuid(),
            'hire_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            // Sosial Media (opsional)
            'whatsapp' => $this->faker->optional()->numerify('628##########'),
            'instagram' => $this->faker->optional()->userName(),
            'tiktok' => $this->faker->optional()->userName(),
            'facebook' => $this->faker->optional()->userName(),
            'linkedin' => $this->faker->optional()->userName(),
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
                'login_id' => 'superadmin',
                'email' => 'superadmin@pstore.com',
                'role' => 'admin',
                'branch_id' => null,
                'division_id' => null,
                'hire_date' => now()->subYears(1),
            ];
        });
    }

    /**
     * State untuk user dengan role tertentu
     */
    public function withRole(string $role)
    {
        return $this->state(function (array $attributes) use ($role) {
            return [
                'role' => $role,
                'login_id' => $role . '_' . Str::random(4),
            ];
        });
    }

    /**
     * State untuk user tanpa divisi
     */
    public function withoutDivision()
    {
        return $this->state(function (array $attributes) {
            return [
                'division_id' => null,
            ];
        });
    }

    /**
     * State untuk user dengan branch tertentu
     */
    public function forBranch($branchId)
    {
        return $this->state(function (array $attributes) use ($branchId) {
            return [
                'branch_id' => $branchId,
            ];
        });
    }

    /**
     * State untuk user dengan division tertentu
     */
    public function forDivision($divisionId)
    {
        return $this->state(function (array $attributes) use ($divisionId) {
            return [
                'division_id' => $divisionId,
            ];
        });
    }
}