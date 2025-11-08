<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua cabang yang sudah kita buat
        $branches = Branch::all();

        if ($branches->isEmpty()) {
            $this->command->error('Tidak ada Cabang. Jalankan BranchSeeder dulu.');
            return;
        }

        // Buat 3 divisi untuk SETIAP cabang
        foreach ($branches as $branch) {
            Division::factory(5)->create([
                'branch_id' => $branch->id
            ]);
        }
    }
}
