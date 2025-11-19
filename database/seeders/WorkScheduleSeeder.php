<?php

namespace Database\Seeders;

use App\Models\WorkSchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        WorkSchedule::create([
            'schedule_name' => 'Jam Kerja Standar',
            'check_in_start' => '07:00',
            'check_in_end' => '10:00',
            'check_out_start' => '16:00',
            'check_out_end' => '19:00',
            'is_default' => true,
            'is_active' => true,
        ]);

        WorkSchedule::create([
            'schedule_name' => 'Jam Kantor Pagi',
            'check_in_start' => '08:00',
            'check_in_end' => '09:00',
            'check_out_start' => '17:00',
            'check_out_end' => '18:00',
            'is_default' => false,
            'is_active' => true,
        ]);

        WorkSchedule::create([
            'schedule_name' => 'Jam Fleksibel',
            'check_in_start' => '07:00',
            'check_in_end' => '11:00',
            'check_out_start' => '15:00',
            'check_out_end' => '19:00',
            'is_default' => false,
            'is_active' => true,
        ]);
    }
}