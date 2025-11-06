<?php

namespace Database\Seeders;

use App\Models\LateNotification;
use Illuminate\Database\Seeder;

class LateNotificationSeeder extends Seeder
{
    public function run()
    {
        // Buat 10 data notifikasi terlambat
        LateNotification::factory(10)->create();
    }
}
