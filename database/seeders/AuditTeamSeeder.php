<?php

namespace Database\Seeders;

use App\Models\AuditTeam;
use Illuminate\Database\Seeder;

class AuditTeamSeeder extends Seeder
{
    public function run()
    {
        // Buat 10 data mapping antara audit dan divisi
        AuditTeam::factory(1)->create();
    }
}
