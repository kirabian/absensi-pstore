<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LateNotification;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\SendFcmNotification; // Pastikan Trait ini ada
use Carbon\Carbon;

class SelfAttendanceController extends Controller
{
    use SendFcmNotification;

    /**
     * Menampilkan Halaman Form Absen (Selfie)
     */
    public function create()
    {
        $user = Auth::user();
        $today = today();

        // 1. Cek apakah sudah ada data absen hari ini
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->first();

        // 2. Jika sudah lengkap (Masuk & Pulang), tolak akses ke halaman ini
        if ($attendance && $attendance->check_out_time) {
            return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini (Masuk & Pulang).');
        }

        // 3. Cek Status Laporan Telat (Hanya validasi saat mau Absen Masuk)
        if (!$attendance) {
            $activeLateStatus = LateNotification::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('created_at', $today)
                ->first();

            if ($activeLateStatus) {
                return redirect()->route('dashboard')->with('error', 'Anda memiliki laporan telat aktif. Harap hapus laporan tersebut di dashboard setelah tiba di kantor untuk melakukan absen.');
            }
        }

        // 4. Tentukan Mode untuk Tampilan (Masuk / Pulang)
        // Jika belum ada data attendance -> Mode Masuk
        // Jika sudah ada data attendance tapi belum checkout -> Mode Pulang
        $mode = $attendance ? 'pulang' : 'masuk';

        return view('user_biasa.absen', compact('mode'));
    }

    /**
     * Memproses Penyimpanan Absen (Masuk & Pulang)
     */
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'photo' => 'required|image|max:5120', // Max 5MB
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = Auth::user();
        $today = today();
        $currentTime = now();

        // Ambil Jadwal Kerja User (untuk cek telat/pulang cepat)
        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        // Cek data absensi hari ini
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->first();

        // Simpan Foto ke Storage
        // Folder: storage/app/public/foto_mandiri
        $path = $request->file('photo')->store('public/foto_mandiri');
        
        // ==============================================================
        // LOGIKA ABSEN MASUK (CHECK-IN)
        // ==============================================================
        if (!$attendance) {
            
            // Cek Keterlambatan berdasarkan Work Schedule
            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                // Jika jam sekarang > batas akhir check in
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                // (Opsional: Tambah toleransi waktu jika perlu, misal ->addMinutes(5))
                if (Carbon::parse($currentTime->format('H:i:s'))->gt($scheduleEnd)) {
                    $isLate = true;
                }
            }

            // Create Data Baru
            Attendance::create([
                'user_id'           => $user->id,
                'branch_id'         => $user->branch_id,
                'check_in_time'     => $currentTime,
                'status'            => 'pending_verification', // Default status mandiri
                'attendance_type'   => 'self',                 // Menandakan ini Selfie Mandiri
                'photo_path'        => $path,                  // Foto Masuk
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'work_schedule_id'  => $workSchedule?->id,
                'is_late_checkin'   => $isLate,
            ]);

            $title = "Verifikasi Masuk (Mandiri)";
            $body = "{$user->name} melakukan absen mandiri (Masuk).";
            $message = 'Berhasil absen masuk. Menunggu verifikasi Audit/Leader.';
        } 

        // ==============================================================
        // LOGIKA ABSEN PULANG (CHECK-OUT)
        // ==============================================================
        else {
            // Pastikan belum absen pulang sebelumnya
            if ($attendance->check_out_time) {
                return redirect()->route('dashboard')->with('error', 'Anda sudah absen pulang sebelumnya.');
            }

            // Cek Pulang Cepat (Early Checkout)
            $isEarly = false;
            if ($workSchedule && $workSchedule->check_out_start) {
                // Jika jam sekarang < jam mulai boleh pulang
                $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                if (Carbon::parse($currentTime->format('H:i:s'))->lt($scheduleStart)) {
                    $isEarly = true;
                }
            }

            // Update Data Lama
            $attendance->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,       // Foto Pulang disimpan di kolom berbeda
                'is_early_checkout' => $isEarly,
                // Catatan: Status biasanya tidak diubah saat pulang, 
                // tetap mengikuti status check-in atau tetap pending jika belum diverifikasi.
            ]);

            $title = "Verifikasi Pulang (Mandiri)";
            $body = "{$user->name} melakukan absen mandiri (Pulang).";
            $message = 'Berhasil absen pulang. Hati-hati di jalan!';
        }

        // Kirim Notifikasi ke Admin/Audit di Branch yang sama
        // Pastikan method sendNotificationToBranchRoles ada di Trait SendFcmNotification
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
            // Jangan gagalkan absen hanya karena notifikasi error, cukup log saja
            \Illuminate\Support\Facades\Log::error('FCM Error: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    /**
     * Menyimpan Laporan Izin Telat
     */
    public function storeLateStatus(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        // Nonaktifkan notifikasi telat sebelumnya jika ada
        LateNotification::where('user_id', $user->id)->update(['is_active' => false]);

        LateNotification::create([
            'user_id'   => $user->id,
            'branch_id' => $user->branch_id,
            'message'   => $request->message,
            'is_active' => true,
        ]);

        $title = "Izin Telat Masuk";
        $body = "{$user->name} dari Divisi " . ($user->division->name ?? 'N/A') . " mengajukan izin telat.";
        
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Error Late: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', 'Laporan telat berhasil dikirim.');
    }

    /**
     * Menghapus Status Laporan Telat (Agar bisa absen)
     */
    public function deleteLateStatus()
    {
        $notification = LateNotification::where('user_id', Auth::id())
            ->where('is_active', true)
            ->whereDate('created_at', today())
            ->first();

        if ($notification) {
            $notification->delete(); // Atau update is_active = false
            return redirect()->route('dashboard')->with('success', 'Laporan telat dihapus. Anda sekarang bisa melakukan absen.');
        }

        return redirect()->route('dashboard')->with('error', 'Laporan telat tidak ditemukan.');
    }
}