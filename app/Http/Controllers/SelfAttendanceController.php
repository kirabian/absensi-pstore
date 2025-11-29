<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LateNotification;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\SendFcmNotification; 
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

        // 1. Cek Sesi Aktif (Lookback 24 jam ke belakang)
        // Mencari data check-in terakhir yang belum ada check-out-nya
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24)) // Batas toleransi 24 jam
            ->latest('check_in_time')
            ->first();

        if ($activeSession) {
            // Jika ada sesi gantung (belum checkout), paksa mode PULANG
            // Meskipun hari sudah berganti (lembur)
            $mode = 'pulang';
            $attendance = $activeSession;
        } else {
            // Jika tidak ada sesi aktif, cek apakah hari ini SUDAH selesai (Masuk & Pulang)?
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->exists();

            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini (Masuk & Pulang).');
            }

            // Jika belum ada sesi aktif dan belum selesai hari ini -> Mode MASUK
            $mode = 'masuk';
            $attendance = null;

            // Cek Status Laporan Telat (Hanya validasi saat mau Absen Masuk)
            $activeLateStatus = LateNotification::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('created_at', $today)
                ->first();

            if ($activeLateStatus) {
                return redirect()->route('dashboard')->with('error', 'Anda memiliki laporan telat aktif. Harap hapus laporan tersebut di dashboard setelah tiba di kantor untuk melakukan absen.');
            }
        }

        return view('user_biasa.absen', compact('mode', 'attendance'));
    }

    /**
     * Memproses Penyimpanan Absen (Masuk & Pulang)
     */
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'photo' => 'required|image|max:51200', // Max 5MB
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = Auth::user();
        $currentTime = now();

        // Ambil Jadwal Kerja User (untuk cek telat/pulang cepat)
        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        // CARI SESI AKTIF (Sama seperti logika di create)
        // Cari absen yang check-out-nya masih kosong dalam 24 jam terakhir
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24))
            ->latest('check_in_time')
            ->first();

        // Simpan Foto ke Storage
        $path = $request->file('photo')->store('public/foto_mandiri');
        
        // ==============================================================
        // LOGIKA ABSEN PULANG (CHECK-OUT) - Dieksekusi jika ada sesi aktif
        // ==============================================================
        if ($attendance) {
            
            // Cek Pulang Cepat (Early Checkout)
            $isEarly = false;

            if ($workSchedule && $workSchedule->check_out_start) {
                $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                $checkOutTimeOnly = Carbon::parse($currentTime->format('H:i:s'));

                // Logika: Jika pulang kurang dari jam jadwal, DAN masih di hari yang sama
                // Jika sudah ganti hari (lewat tengah malam), otomatis TIDAK Early Checkout (karena lembur)
                $isSameDay = $attendance->check_in_time->isSameDay($currentTime);

                if ($isSameDay && $checkOutTimeOnly->lt($scheduleStart)) {
                    $isEarly = true;
                }
            }

            // Update Data Lama (Menutup sesi)
            $attendance->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,       // Foto Pulang
                'is_early_checkout' => $isEarly,
                // Status absensi (Hadir/Telat) tidak berubah saat pulang
            ]);

            // Cek apakah ini lembur lintas hari untuk pesan notifikasi
            $isCrossDay = $attendance->check_in_time->format('Y-m-d') !== $currentTime->format('Y-m-d');
            $noteLembur = $isCrossDay ? " (Lembur Lintas Hari)" : "";

            $title = "Verifikasi Pulang (Mandiri)";
            $body = "{$user->name} melakukan absen mandiri (Pulang){$noteLembur}.";
            $message = "Berhasil absen pulang{$noteLembur}. Hati-hati di jalan!";
        }

        // ==============================================================
        // LOGIKA ABSEN MASUK (CHECK-IN) - Dieksekusi jika TIDAK ada sesi aktif
        // ==============================================================
        else {
            
            // Cek apakah user mencoba absen masuk lagi padahal sudah "Selesai" hari ini?
            // (Optional safety check)
            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->exists();

            if ($alreadyFinished) {
                return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            // Cek Keterlambatan berdasarkan Work Schedule
            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                // Jika jam sekarang > batas akhir check in
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                
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

        // Kirim Notifikasi ke Admin/Audit di Branch yang sama
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
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
            $notification->delete(); 
            return redirect()->route('dashboard')->with('success', 'Laporan telat dihapus. Anda sekarang bisa melakukan absen.');
        }

        return redirect()->route('dashboard')->with('error', 'Laporan telat tidak ditemukan.');
    }
}