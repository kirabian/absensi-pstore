<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run()
    {
        // Daftar Divisi Global (Berlaku untuk semua cabang)
        $divisions = [
            'Freelance', 'Cheff', 'Creative', 'Purchasing', 'Design interior',
            'Teknisi Handphone', 'Security', 'Training', 'Managament', 'Leader',
            'Admin Sosial Media', 'Promotor SAMSUNG', 'Promotor TAM', 
            'Promotor XIAOMI', 'Promotor REALME', 'Promotor INFINIX', 
            'Promotor VIVO', 'Promotor OPPO', 'Audit', 'Marketing', 
            'Customer services', 'Team IT', 'Finance'
        ];

        foreach ($divisions as $divisionName) {
            Division::firstOrCreate([
                'name' => $divisionName
            ]);
        }
    }
}