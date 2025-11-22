<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Branch; // <--- Import Model Branch
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition()
    {
        $divisions = [
            'Freelance', 'Cheff', 'Creative', 'Purchasing', 'Design interior',
            'Teknisi Handphone', 'Security', 'Training', 'Managament', 'Leader',
            'Admin Sosial Media', 'Promotor SAMSUNG', 'Promotor TAM', 'Promotor XIAOMI',
            'Promotor REALME', 'Promotor INFINIX', 'Promotor VIVO', 'Promotor OPPO',
            'Audit', 'Marketing', 'Customer services', 'Team IT', 'Finance'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($divisions),
            
            // Logic: Buat Branch baru (factory) ATAU ambil random kalau mau
            'branch_id' => Branch::factory(), 
        ];
    }
}