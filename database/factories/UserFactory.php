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
        
        // Ambil division random (sekarang independent dari branch)
        $division = Division::inRandomOrder()->first();

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

    /**
     * State untuk user dengan role tertentu
     */
    public function withRole(string $role)
    {
        return $this->state(function (array $attributes) use ($role) {
            return [
                'role' => $role,
            ];
        });
    }

    /**
     * State untuk user tanpa divisi (Admin Cabang, Security)
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