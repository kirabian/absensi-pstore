<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // Buat 5 data absensi mandiri ('pending_verification')
        Attendance::factory(5)->create();

        // Buat 5 data absensi yang di-scan security ('verified')
        Attendance::factory(5)->scannedBySecurity()->create();
    }
}
