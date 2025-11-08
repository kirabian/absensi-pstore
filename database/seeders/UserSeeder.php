<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Division;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat 1 Super Admin (tidak punya cabang)
        User::factory()->create([
            'name' => 'Super Admin PStore',
            'email' => 'superadmin@pstore.com',
            'role' => 'admin',
            'branch_id' => null,
            'division_id' => null,
        ]);

        // Ambil semua cabang (yang sudah punya divisi)
        $branches = Branch::with('divisions')->get();

        if ($branches->isEmpty()) {
            $this->command->error('Tidak ada Cabang/Divisi. Jalankan BranchSeeder & DivisionSeeder dulu.');
            return;
        }

        // 2. Buat staf untuk SETIAP cabang
        foreach ($branches as $branch) {
            // Ambil divisi pertama di cabang ini untuk referensi
            $division = $branch->divisions->first();
            if (!$division) continue; // Lewati jika cabang ini tidak punya divisi

            // Buat 1 Admin Cabang
            User::factory()->create([
                'name' => 'Admin ' . $branch->name,
                'email' => 'admin.' . strtolower(str_replace(' ', '', $branch->name)) . '@pstore.com',
                'role' => 'admin',
                'branch_id' => $branch->id,
                'division_id' => null, // Admin cabang tidak punya divisi
            ]);

            // Buat 1 Audit
            User::factory()->create([
                'name' => 'Audit ' . $branch->name,
                'email' => 'audit.' . strtolower(str_replace(' ', '', $branch->name)) . '@pstore.com',
                'role' => 'audit',
                'branch_id' => $branch->id,
                'division_id' => $division->id, // Audit kita masukkan ke divisi pertama
            ]);

            // Buat 1 Security
            User::factory()->create([
                'name' => 'Security ' . $branch->name,
                'email' => 'security.' . strtolower(str_replace(' ', '', $branch->name)) . '@pstore.com',
                'role' => 'security',
                'branch_id' => $branch->id,
                'division_id' => null, // Security tidak punya divisi
            ]);
        }

        // 3. Buat 1 Tim Lengkap (sesuai permintaan 1 Leader + 7 Anggota)
        // Kita akan pakai Cabang pertama dan Divisi pertama
        $firstBranch = $branches->first();
        $firstDivision = $firstBranch->divisions->first();

        if ($firstDivision) {
            // Buat 1 Leader
            User::factory()->create([
                'name' => 'Leader Tim A',
                'email' => 'leader.tim.a@pstore.com',
                'role' => 'leader',
                'branch_id' => $firstBranch->id,
                'division_id' => $firstDivision->id,
            ]);

            // Buat 7 Anggota Tim (User Biasa)
            User::factory(7)->create([
                'role' => 'user_biasa',
                'branch_id' => $firstBranch->id,
                'division_id' => $firstDivision->id,
            ]);
        }
    }
}
