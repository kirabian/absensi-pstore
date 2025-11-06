<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditTeamFactory extends Factory
{
    public function definition()
    {
        return [
            // Ambil ID user yang rolenya 'audit' secara acak
            'user_id' => User::where('role', 'audit')->inRandomOrder()->first()->id,

            // Ambil ID divisi secara acak
            'division_id' => Division::inRandomOrder()->first()->id,
        ];
    }
}
