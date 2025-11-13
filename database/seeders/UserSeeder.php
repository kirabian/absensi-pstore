<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Division;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
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
            $this->command->error('Tidak ada Cabang/Divisi. Pastikan BranchSeeder & DivisionSeeder sudah dijalankan.');
            return;
        }

        // 2. Buat staf UNTUK SETIAP CABANG
        foreach ($branches as $branch) {

            // Ambil divisi pertama di cabang ini untuk referensi
            $division = $branch->divisions->first();
            if (!$division) continue; // Lewati jika cabang ini (anehnya) tidak punya divisi

            // Buat 1 Admin Cabang
            User::factory()->create([
                'name' => 'Admin ' . $branch->name,
                'email' => 'admin.' . strtolower(preg_replace('/[^a-z0-9]/i', '', $branch->name)) . '@pstore.com',
                'role' => 'admin',
                'branch_id' => $branch->id,
                'division_id' => null, // Admin cabang tidak punya divisi
            ]);

            // Buat 1 Audit
            User::factory()->create([
                'name' => 'Audit ' . $branch->name,
                'email' => 'audit.' . strtolower(preg_replace('/[^a-z0-9]/i', '', $branch->name)) . '@pstore.com',
                'role' => 'audit',
                'branch_id' => $branch->id,
                'division_id' => $division->id, // Audit kita masukkan ke divisi pertama
            ]);

            // Buat 1 Security
            User::factory()->create([
                'name' => 'Security ' . $branch->name,
                'email' => 'security.' . strtolower(preg_replace('/[^a-z0-9]/i', '', $branch->name)) . '@pstore.com',
                'role' => 'security',
                'branch_id' => $branch->id,
                'division_id' => null, // Security tidak punya divisi
            ]);

            // --- PERBAIKAN DI SINI ---
            // 3. Buat 1 Tim Lengkap (1 Leader + 7 Anggota) UNTUK SETIAP CABANG
            // Kita akan pakai Divisi pertama di cabang ini

            // Buat 1 Leader
            User::factory()->create([
                'name' => 'Leader Tim ' . $branch->name,
                'email' => 'leader.' . strtolower(preg_replace('/[^a-z0-9]/i', '', $branch->name)) . '@pstore.com',
                'role' => 'leader',
                'branch_id' => $branch->id,
                'division_id' => $division->id,
            ]);

            // Buat 7 Anggota Tim (User Biasa)
            User::factory(7)->create([
                'role' => 'user_biasa',
                'branch_id' => $branch->id,
                'division_id' => $division->id,
            ]);
            // --- BATAS PERBAIKAN ---
        }
    }
}
