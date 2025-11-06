<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Urutan ini SANGAT PENTING

        // 1. Buat Divisi dulu
        $this->call(DivisionSeeder::class);

        // 2. Buat User (karena butuh ID Divisi)
        $this->call(UserSeeder::class);

        // 3. Sisanya bebas, karena butuh ID User & Divisi
        $this->call(AuditTeamSeeder::class);
        $this->call(AttendanceSeeder::class);
        $this->call(LateNotificationSeeder::class);
    }
}
