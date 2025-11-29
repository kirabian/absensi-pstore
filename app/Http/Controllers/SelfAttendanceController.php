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
// Import Facade Cloudinary
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
     * Memproses Penyimpanan Absen dengan Efek Cloudinary
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
        // PROSES UPLOAD KE CLOUDINARY (DEBUG MODE)
        // ==========================================================
        try {
            // Pastikan nama ini SAMA PERSIS dengan di Cloudinary
            $overlayPublicId = 'topeng_vader'; 
            
            $timestampText = $currentTime->locale('id')->translatedFormat('d M Y H:i');

            // Kita gunakan Array Syntax standar Cloudinary Laravel
            $uploadedFile = Cloudinary::upload($request->file('photo')->getRealPath(), [
                'folder' => 'absensi_pstore_effects',
                'transformation' => [
                    // LAYER 1: MASKER
                    [
                        'overlay' => $overlayPublicId,
                        'gravity' => 'faces',           // Deteksi wajah
                        'width'   => 1.3,               // Lebar 1.3x wajah
                        'flags'   => 'region_relative', // Agar ikut ukuran wajah
                        'crop'    => 'scale'
                    ],
                    // LAYER 1.5: APPLY LAYER (Penting: Flag layer_apply dipisah agar aman)
                    [
                        'flags' => 'layer_apply'
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
                        'background' => '#00000090'
                    ],
                    // LAYER 2.5: APPLY LAYER TEXT
                    [
                        'flags' => 'layer_apply'
                    ]
                ]
            ]);

            $path = $uploadedFile->getSecurePath();

        } catch (\Exception $e) {
            // --- BAGIAN INI UNTUK MELIHAT ERRORNYA ---
            dd("STOP! Error terjadi:", $e->getMessage()); 
            // Nanti kalau sudah benar, kembalikan ke: return back()->with('error', ...);
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
                'photo_out_path'    => $path,       // URL Foto Cloudinary (Pulang)
                'is_early_checkout' => $isEarly,
            ]);

            $isCrossDay = $attendance->check_in_time->format('Y-m-d') !== $currentTime->format('Y-m-d');
            $noteLembur = $isCrossDay ? " (Lembur Lintas Hari)" : "";

            $title = "Verifikasi Pulang (Mandiri)";
            $body = "{$user->name} melakukan absen mandiri (Pulang){$noteLembur} dengan Efek Wajah.";
            $message = "Berhasil absen pulang{$noteLembur}. Foto efek berhasil disimpan!";
        }

        // ==============================================================
        // LOGIKA ABSEN MASUK (CHECK-IN)
        // ==============================================================
        else {
            // Safety check: apakah sudah selesai hari ini?
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
                'photo_path'        => $path,          // URL Foto Cloudinary (Masuk)
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'work_schedule_id'  => $workSchedule?->id,
                'is_late_checkin'   => $isLate,
            ]);

            $title = "Verifikasi Masuk (Mandiri)";
            $body = "{$user->name} melakukan absen mandiri (Masuk) dengan Efek Wajah.";
            $message = 'Berhasil absen masuk. Foto efek berhasil disimpan! Menunggu verifikasi.';
        }

        // Kirim Notifikasi FCM
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
        }

        // Redirect ke Dashboard dengan membawa URL foto agar bisa dilihat user di alert
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