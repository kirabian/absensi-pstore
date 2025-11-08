<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // URUTAN INI SANGAT PENTING!
        $this->call([
            BranchSeeder::class,      // 1. Buat Cabang dulu
            DivisionSeeder::class,    // 2. Buat Divisi (butuh Cabang)
            UserSeeder::class,        // 3. Buat User (butuh Cabang & Divisi)

            // Seeders lama Anda (butuh User & Divisi)
            AuditTeamSeeder::class,
            AttendanceSeeder::class,
            LateNotificationSeeder::class,
        ]);
    }
}
