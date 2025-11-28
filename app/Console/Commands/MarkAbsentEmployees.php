<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest; // Model yang Anda kirim
use App\Models\WorkSchedule; // Asumsi ada model ini
use Carbon\Carbon;

class MarkAbsentEmployees extends Command
{
    /**
     * Nama command untuk dijalankan di terminal/scheduler
     */
    protected $signature = 'attendance:mark-absent';

    /**
     * Deskripsi command
     */
    protected $description = 'Cek user yang tidak absen kemarin dan tandai sebagai Alpha';

    /**
     * Eksekusi logika
     */
    public function handle()
    {
        // 1. Tentukan tanggal pengecekan (KEMARIN)
        // Karena script ini jalan jam 01:00 dini hari, kita cek tanggal sebelumnya.
        $yesterday = Carbon::yesterday();
        
        $this->info("Memulai proses Auto-Alpha untuk tanggal: " . $yesterday->format('d-m-Y'));

        // 2. Ambil semua karyawan aktif
        // Sesuaikan filter role jika admin/super_admin tidak perlu absen
        $users = User::where('role', '!=', 'super_admin')->get(); 

        $alphaCount = 0;

        foreach ($users as $user) {
            
            // --- CEK 1: Apakah user SUDAH ABSEN MASUK kemarin? ---
            $hasAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $yesterday)
                ->exists();

            if ($hasAttendance) {
                continue; // Skip, dia hadir (atau telat tapi hadir)
            }

            // --- CEK 2: Apakah user SEDANG CUTI / SAKIT / IZIN (Full Day)? ---
            // Menggunakan model LeaveRequest Anda
            $isOnLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved') // Hanya yang disetujui
                ->where('type', '!=', 'telat') // PENTING: 'telat' tidak dianggap libur full day
                ->where(function($query) use ($yesterday) {
                    // Logika: Tanggal "Kemarin" berada di dalam range Start s/d End
                    $query->whereDate('start_date', '<=', $yesterday)
                          ->whereDate('end_date', '>=', $yesterday);
                })
                ->exists();

            if ($isOnLeave) {
                // Dia tidak absen karena memang izin resmi (Sakit/Cuti)
                $this->info("User {$user->name} sedang izin/cuti. Skip.");
                continue; 
            }

            // --- CEK 3: Opsional (Cek Hari Libur / Akhir Pekan) ---
            // Jika kantor libur Sabtu/Minggu, uncomment kode di bawah:
            /*
            if ($yesterday->isWeekend()) {
                 continue;
            }
            */

            // --- EKSEKUSI: BUAT DATA ALPHA ---
            // Jika sampai sini, berarti: Tidak Hadir DAN Tidak Izin Resmi
            try {
                Attendance::create([
                    'user_id'           => $user->id,
                    'branch_id'         => $user->branch_id, // Pastikan user punya kolom ini
                    
                    // Kita set waktunya 00:00 di tanggal kemarin
                    'check_in_time'     => $yesterday->copy()->setTime(0, 0, 0),
                    'check_out_time'    => $yesterday->copy()->setTime(0, 0, 0),
                    
                    'status'            => 'verified', // Langsung verified karena system
                    'presence_status'   => 'Alpha',    // Status Alpha
                    'attendance_type'   => 'system',   // Penanda dibuat oleh sistem
                    'photo_path'        => null,
                    'is_late_checkin'   => false,
                    'is_early_checkout' => false,
                    'audit_note'        => 'System Auto-Generate: Tidak ada absensi hingga pergantian hari.',
                ]);

                $this->info("User {$user->name} -> Ditetapkan ALPHA.");
                $alphaCount++;

            } catch (\Exception $e) {
                $this->error("Gagal memproses {$user->name}: " . $e->getMessage());
            }
        }

        $this->info("Selesai. Total karyawan Alpha: $alphaCount");
    }
}