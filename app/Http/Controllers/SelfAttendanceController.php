<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LateNotification;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Traits\SendFcmNotification; 
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24))
            ->latest('check_in_time')
            ->first();

        if ($activeSession) {
            // Mode PULANG
            $mode = 'pulang';
            $attendance = $activeSession;
        } else {
            // Cek apakah hari ini SUDAH selesai?
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->exists();

            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini (Masuk & Pulang).');
            }

            // Mode MASUK
            $mode = 'masuk';
            $attendance = null;

            // Cek Laporan Telat
            $activeLateStatus = LateNotification::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('created_at', $today)
                ->first();

            if ($activeLateStatus) {
                return redirect()->route('dashboard')->with('error', 'Anda memiliki laporan telat aktif. Harap hapus laporan tersebut setelah tiba di kantor.');
            }
        }

        return view('user_biasa.absen', compact('mode', 'attendance'));
    }

    /**
     * Memproses Penyimpanan Absen
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'photo' => 'required|image|max:10240', // Max 10MB
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = Auth::user();
        $currentTime = now();

        // Ambil Jadwal Kerja
        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        // Cari Sesi Absen Aktif
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24))
            ->latest('check_in_time')
            ->first();

        // ==========================================================
        // PROSES UPLOAD KE CLOUDINARY + EFEK
        // ==========================================================
        try {
            // ID Gambar Topeng di Cloudinary (Ganti jika nama file beda)
            $overlayPublicId = 'topeng_vader'; 
            
            // Format Waktu untuk Watermark
            $timestampText = $currentTime->locale('id')->translatedFormat('d M Y H:i');

            $uploadedFile = Cloudinary::upload($request->file('photo')->getRealPath(), [
                'folder' => 'absensi_pstore_effects',
                'transformation' => [
                    // LAYER 1: MASKER WAJAH (VADER)
                    // Menggunakan raw_transformation string agar lebih presisi menangani flag wajah
                    [
                        'raw_transformation' => "l_$overlayPublicId/fl_layer_apply,fl_region_relative,g_faces,w_1.2,y_-0.05"
                    ],

                    // LAYER 2: WATERMARK TEXT
                    [
                        'overlay' => [
                            'font_family' => 'Arial',
                            'font_size'   => 28,
                            'font_weight' => 'bold',
                            'text'        => $timestampText
                        ],
                        'gravity'    => 'south',
                        'y'          => 20,
                        'color'      => '#FFFFFF',
                        'background' => '#00000090',
                        'flags'      => 'layer_apply'
                    ]
                ]
            ]);

            // Ambil URL hasil yang sudah ada efeknya
            $path = $uploadedFile->getSecurePath();

        } catch (\Exception $e) {
            // Jika gagal upload (koneksi/config salah), catat log dan beritahu user
            Log::error('Cloudinary Upload Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses upload foto. Pastikan koneksi internet stabil. Error: ' . $e->getMessage());
        }

        // ==============================================================
        // LOGIKA ABSEN PULANG (CHECK-OUT)
        // ==============================================================
        if ($attendance) {
            
            $isEarly = false;
            if ($workSchedule && $workSchedule->check_out_start) {
                $scheduleStart = Carbon::parse($workSchedule->check_out_start);
                $checkOutTimeOnly = Carbon::parse($currentTime->format('H:i:s'));
                $isSameDay = $attendance->check_in_time->isSameDay($currentTime);

                if ($isSameDay && $checkOutTimeOnly->lt($scheduleStart)) {
                    $isEarly = true;
                }
            }

            // Update Data Lama
            $attendance->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,       // URL Foto Cloudinary
                'is_early_checkout' => $isEarly,
            ]);

            $isCrossDay = $attendance->check_in_time->format('Y-m-d') !== $currentTime->format('Y-m-d');
            $noteLembur = $isCrossDay ? " (Lembur Lintas Hari)" : "";

            $title = "Verifikasi Pulang (Mandiri)";
            $body = "{$user->name} melakukan absen mandiri (Pulang){$noteLembur}.";
            $message = "Berhasil absen pulang{$noteLembur}.";
        }

        // ==============================================================
        // LOGIKA ABSEN MASUK (CHECK-IN)
        // ==============================================================
        else {
            // Safety check
            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->exists();

            if ($alreadyFinished) {
                return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
            }

            // Cek Telat
            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
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
                'status'            => 'pending_verification',
                'attendance_type'   => 'self',
                'photo_path'        => $path,          // URL Foto Cloudinary
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'work_schedule_id'  => $workSchedule?->id,
                'is_late_checkin'   => $isLate,
            ]);

            $title = "Verifikasi Masuk (Mandiri)";
            $body = "{$user->name} melakukan absen mandiri (Masuk).";
            $message = 'Berhasil absen masuk. Menunggu verifikasi.';
        }

        // Kirim Notifikasi FCM
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
        }

        // Redirect ke Dashboard & Bawa URL foto ke session agar bisa dilihat di alert
        return redirect()->route('dashboard')
            ->with('success', $message)
            ->with('photo_url', $path);
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

        LateNotification::where('user_id', $user->id)->update(['is_active' => false]);

        LateNotification::create([
            'user_id'   => $user->id,
            'branch_id' => $user->branch_id,
            'message'   => $request->message,
            'is_active' => true,
        ]);

        $title = "Izin Telat Masuk";
        $body = "{$user->name} mengajukan izin telat.";
        
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
            Log::error('FCM Error Late: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', 'Laporan telat berhasil dikirim.');
    }

    /**
     * Menghapus Status Laporan Telat
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