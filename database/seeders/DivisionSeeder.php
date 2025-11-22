<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Branch; // <--- Jangan lupa import ini
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run()
    {
        // 1. Ambil semua ID Branch yang ada di database
        $branchIds = Branch::pluck('id');

        // Cek: Kalau tabel branches masih kosong, buat dummy branch dulu biar gak error
        if ($branchIds->isEmpty()) {
            $this->command->info('Tabel branches kosong. Membuat 1 cabang dummy...');
            $branch = Branch::create([
                'name' => 'Pusat (Default)',
                'address' => 'Jakarta'
            ]);
            $branchIds->push($branch->id);
        }

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

        foreach ($divisions as $divisionName) {
            // Gunakan firstOrCreate agar tidak duplikat jika seeder dijalankan 2x
            Division::firstOrCreate(
                ['name' => $divisionName], // Cek berdasarkan nama
                [
                    // Ambil ID Branch secara acak dari list yg ada
                    'branch_id' => $branchIds->random() 
                ]
            );
        }
    }
}