<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            BranchSeeder::class,
            DivisionSeeder::class,
            UserSeeder::class,
            // AuditTeamSeeder::class, // Tetap dikomen
        ]);
    }
}