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
        $today = today(); // Tanggal hari ini (00:00:00)

        // 1. Cek Sesi HARI INI
        // Kita hanya mencari sesi yang Check-In nya dilakukan HARI INI dan belum Check-Out.
        // Sesi kemarin yang lupa checkout diabaikan di sini (akan diurus di proses store).
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->whereNull('check_out_time')
            ->first();

        if ($activeSession) {
            // Jika hari ini sudah masuk dan belum pulang -> Mode PULANG
            $mode = 'pulang';
            $attendance = $activeSession;
        } else {
            // Jika tidak ada sesi aktif hari ini, cek apakah SUDAH SELESAI hari ini?
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->exists();

            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini (Masuk & Pulang).');
            }

            // Jika belum ada sesi hari ini -> Mode MASUK
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

        // Ambil Jadwal Kerja User
        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        // Cari Sesi HARI INI yang belum checkout
        $attendanceToday = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', today())
            ->whereNull('check_out_time')
            ->first();

        // Simpan Foto ke Storage
        $path = $request->file('photo')->store('public/foto_mandiri');

        // ==============================================================
        // LOGIKA ABSEN PULANG (CHECK-OUT) - Jika ada sesi HARI INI
        // ==============================================================
        if ($attendanceToday) {

            // Cek Pulang Cepat (Early Checkout)
            $isEarly = false;

            if ($workSchedule && $workSchedule->check_out_start) {
                $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                $checkOutTimeOnly = Carbon::parse($currentTime->format('H:i:s'));

                // Jika jam sekarang kurang dari jadwal pulang -> Early
                if ($checkOutTimeOnly->lt($scheduleStart)) {
                    $isEarly = true;
                }
            }

            // Update Data (Menutup sesi hari ini)
            $attendanceToday->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,
                'is_early_checkout' => $isEarly,
            ]);

            $title = "Verifikasi Pulang (Mandiri)";
            $body = "{$user->name} melakukan absen mandiri (Pulang).";
            $message = "Berhasil absen pulang. Hati-hati di jalan!";
        }

        // ==============================================================
        // LOGIKA ABSEN MASUK (CHECK-IN) - Jika TIDAK ada sesi HARI INI
        // ==============================================================
        else {

            // --- [FITUR AUTO RESET] ---
            // Cari sesi "Gantung" dari masa lalu (kemarin atau sebelumnya) yang lupa di-checkout
            $hangingSessions = Attendance::where('user_id', $user->id)
                ->whereNull('check_out_time')
                ->whereDate('check_in_time', '<', today()) // Tanggal sebelum hari ini
                ->get();

            foreach ($hangingSessions as $hanging) {
                // Tutup otomatis sesi kemarin.
                // Kita set waktu checkout ke akhir hari (23:59:59) pada tanggal check-in tersebut.
                $autoOutTime = Carbon::parse($hanging->check_in_time)->endOfDay();

                $hanging->update([
                    'check_out_time' => $autoOutTime,
                    'notes' => 'Auto-closed by system (Lupa Absen Pulang)',
                    // Tidak ada foto pulang
                ]);
            }
            // --------------------------

            // Safety Check: Double check takutnya user nge-spam tombol
            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->exists();

            if ($alreadyFinished) {
                return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            // Cek Keterlambatan
            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);

                if (Carbon::parse($currentTime->format('H:i:s'))->gt($scheduleEnd)) {
                    $isLate = true;
                }
            }

            // Create Data Baru (Masuk Hari Ini)
            Attendance::create([
                'user_id'           => $user->id,
                'branch_id'         => $user->branch_id,
                'check_in_time'     => $currentTime,
                'status'            => 'pending_verification',
                'attendance_type'   => 'self',
                'photo_path'        => $path,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'work_schedule_id'  => $workSchedule?->id,
                'is_late_checkin'   => $isLate,
            ]);

            $title = "Verifikasi Masuk (Mandiri)";
            $body = "{$user->name} melakukan absen mandiri (Masuk).";
            $message = 'Berhasil absen masuk. Menunggu verifikasi Audit/Leader.';
        }

        // Kirim Notifikasi
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Error: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    /**
     * Menyimpan Laporan Izin Telat (SAMA SEPERTI SEBELUMNYA)
     */
    public function storeLateStatus(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $user = Auth::user();

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
     * Menghapus Status Laporan Telat (SAMA SEPERTI SEBELUMNYA)
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

    /**
     * Memproses Lewati Absen Pulang (Force Close sesi kemarin)
     */
    public function skipCheckOut($id)
    {
        $user = Auth::user();

        // Cari data absensi berdasarkan ID yang dikirim
        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('check_out_time') // Pastikan memang belum checkout
            ->first();

        if ($attendance) {
            // Set waktu pulang ke akhir hari dari tanggal masuk (23:59:59)
            // Agar jam kerjanya terhitung full hari itu, tapi tidak nyebrang ke hari ini
            $autoOutTime = Carbon::parse($attendance->check_in_time)->endOfDay();

            $attendance->update([
                'check_out_time' => $autoOutTime,
                'photo_out_path' => null, // Tidak ada foto
                'notes'          => 'User lupa absen pulang (Sesi ditutup manual via Dashboard)',
                // Status tidak diubah ke pending, biarkan status terakhir (biasanya present/late)
            ]);

            return redirect()->route('dashboard')->with('success', 'Sesi kemarin telah ditutup. Silakan absen masuk untuk hari ini.');
        }

        return redirect()->route('dashboard')->with('error', 'Sesi tidak ditemukan atau sudah ditutup.');
    }
}
