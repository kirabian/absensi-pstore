<?php

namespace Database\Factories;

use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition()
    {
        // Daftar nama divisi (Master Data)
        $divisions = [
            'Freelance',
            'Cheff',
            'Creative',
            'Purchasing',
            'Design interior',
            'Teknisi Handphone',
            'Security',
            'Training',
            'Managament',
            'Leader',
            'Admin Sosial Media',
            'Promotor SAMSUNG',
            'Promotor TAM',
            'Promotor XIAOMI',
            'Promotor REALME',
            'Promotor INFINIX',
            'Promotor VIVO',
            'Promotor OPPO',
            'Audit',
            'Marketing',
            'Customer services',
            'Team IT',
            'Finance'
        ];

        return [
            // Kita gunakan unique() agar saat di-generate tidak ada nama kembar
            // HAPUS bagian 'branch_id' karena sudah tidak dipakai
            'name' => $this->faker->unique()->randomElement($divisions),
        ];
    }
}