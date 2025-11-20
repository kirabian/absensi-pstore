<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run()
    {
        $divisions = [
            'Tim Marketing',
            'Tim Sales', 
            'Tim IT',
            'Tim HRD',
            'Tim Finance',
            'Tim Operations',
            'Tim Customer Service',
            'Tim Production'
        ];

        foreach ($divisions as $divisionName) {
            Division::create([
                'name' => $divisionName
            ]);
        }
    }
}