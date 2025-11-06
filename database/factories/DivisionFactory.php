<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    public function definition()
    {
        return [
            // Membuat nama divisi palsu seperti "Tim Marketing", "Tim Keuangan"
            'name' => 'Tim ' . $this->faker->jobTitle(),
        ];
    }
}
