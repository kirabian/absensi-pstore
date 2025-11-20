<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $branches = [
            'Pstore Aceh',
            'Pstore Bali',
            'Pstore Balikpapan',
            'Pstore Bandung lama',
            'Pstore Bandung Neo',
            'Pstore Bandung Uber',
            'Pstore Bangkabelitung',
            'Pstore Banjarmasin',
            'Pstore Batam batu aji',
            'Pstore Batam Center',
            'Pstore Bekasi',
            'Pstore Bengkulu',
            'Pstore Bogor',
            'Pstore Buaran',
            'Pstore Cianjur',
            'Pstore Cibinong',
            'Pstore Cibubur',
            'Pstore Cikarang',
            'Pstore Cikupa',
            'Pstore Cilegon',
            'Pstore Ciputat',
            'Pstore Cirebon',
            'Pstore Depok',
            'Pstore Depok Gotik',
            'Pstore Gorontalo',
            'Pstore Jambi',
            'Pstore Jogja',
            'Pstore Karawang',
            'Pstore Kelapa Gading',
            'Pstore Lampung',
            'Pstore Lenteng Agung',
            'Pstore Lombok',
            'Pstore Makassar',
            'Pstore Makassar New',
            'Pstore Malang',
            'Pstore Manado',
            'Pstore Medan',
            'Pstore Padang',
            'Pstore Palangkaraya',
            'Pstore Palembang',
            'Pstore Palu',
            'Pstore Pamulang',
            'Pstore Pekanbaru',
            'Pstore Pontianak',
            'PS big jakarta', // Tanpa Pstore
            'PS new jakarta', // Tanpa Pstore
            'Pstore Purwokerto',
            'Pstore Qcell jakarta',
            'Pstore Salemba',
            'Pstore Samarinda',
            'Pstore Sangatta',
            'Pstore Semarang',
            'Pstore Serang Banten',
            'Pstore Shopee',
            'Pstore Slipi',
            'Pstore Solo',
            'Pstore Sukabumi',
            'Pstore Sunter',
            'Pstore Surabaya',
            'Pstore Tangerang',
            'Pstore Tarakan',
            'Pstore Tebet',
        ];

        $branchData = [];
        foreach ($branches as $branchName) {
            $branchData[] = [
                'name' => $branchName,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('branches')->insert($branchData);
    }
}