<?php

namespace Database\Factories;

use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition()
    {
        return [
            'name' => 'Tim ' . $this->faker->unique()->jobTitle(),
        ];
    }
}