<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run()
    {
        // Buat 5 cabang dummy
        Branch::factory(61)->create();
    }
}
