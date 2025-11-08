<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
// Jangan import Branch di sini, biarkan Seeder yang mengaturnya

class DivisionFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => 'Tim ' . $this->faker->jobTitle(),
            // branch_id akan kita isi lewat Seeder
        ];
    }
}
