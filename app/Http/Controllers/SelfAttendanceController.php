<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LateNotification;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Ini bisa dihapus jika tidak dipakai lagi
use App\Traits\SendFcmNotification; 
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary; // <--- TAMBAHAN PENTING

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
            $mode = 'pulang';
            $attendance = $activeSession;
        } else {
            $finishedToday = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $today)
                ->whereNotNull('check_out_time')
                ->exists();

            if ($finishedToday) {
                return redirect()->route('dashboard')->with('success', 'Anda sudah menyelesaikan absensi hari ini (Masuk & Pulang).');
            }

            $mode = 'masuk';
            $attendance = null;

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
     * Memproses Penyimpanan Absen (Masuk & Pulang) dengan Cloudinary
     */
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'photo' => 'required|image|max:10240', // Max 10MB (Cloudinary kuat handle file besar)
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = Auth::user();
        $currentTime = now();

        // Ambil Jadwal Kerja User
        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        // CARI SESI AKTIF
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24))
            ->latest('check_in_time')
            ->first();

        // ==========================================================
        // PROSES UPLOAD KE CLOUDINARY + WATERMARK
        // ==========================================================
        
        // 1. Siapkan Teks Timestamp
        $timestampText = $currentTime->locale('id')->translatedFormat('l, d F Y H:i') . ' WIB';

        // 2. Upload & Transformasi
        // Gunakan try-catch untuk handle jika internet putus saat upload
        try {
            $uploadedFile = Cloudinary::upload($request->file('photo')->getRealPath(), [
                'folder' => 'absensi_pstore', // Nama folder di Cloudinary
                'transformation' => [
                    // A. Resize agar tidak terlalu berat (Optional)
                    ['width' => 1000, 'crop' => 'limit'],
                    
                    // B. Layer Text Watermark (Timestamp)
                    [
                        'overlay' => [
                            'font_family' => 'Arial',
                            'font_size'   => 28,
                            'font_weight' => 'bold',
                            'text'        => $timestampText
                        ],
                        'color'      => '#FFFFFF', // Warna Teks Putih
                        'background' => '#00000090', // Background Hitam Transparan
                        'gravity'    => 'south',   // Posisi di Bawah
                        'y'          => 20,        // Jarak dari bawah 20px
                        'width'      => 1000,      // Lebar background text menyesuaikan gambar
                        'crop'       => 'fit'
                    ],

                    /** * OPSI TAMBAHAN: MASKER WAJAH (VADER)
                     * Jika ingin pakai masker, uncomment kode di bawah ini 
                     * dan pastikan 'vader_mask_cutout' ada di Cloudinary Anda.
                     */
                    /*
                    [
                        'overlay' => 'vader_mask_cutout',
                        'flags'   => 'layer_apply',
                        'gravity' => 'faces', 
                        'width'   => 1.1, // Scale relative to face
                        'flags'   => 'relative' // Agar ukuran ikut wajah
                    ]
                    */
                ]
            ]);

            // Ambil URL aman (https) dari hasil upload
            $path = $uploadedFile->getSecurePath();

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal upload foto ke Cloudinary: ' . $e->getMessage());
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

            // Update Data Lama (Menutup sesi)
            $attendance->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,       // <--- INI SEKARANG URL CLOUDINARY
                'is_early_checkout' => $isEarly,
            ]);

            $isCrossDay = $attendance->check_in_time->format('Y-m-d') !== $currentTime->format('Y-m-d');
            $noteLembur = $isCrossDay ? " (Lembur Lintas Hari)" : "";

            $title = "Verifikasi Pulang (Mandiri)";
            $body = "{$user->name} melakukan absen mandiri (Pulang){$noteLembur}.";
            $message = "Berhasil absen pulang{$noteLembur}. Hati-hati di jalan!";
        }

        // ==============================================================
        // LOGIKA ABSEN MASUK (CHECK-IN)
        // ==============================================================
        else {
            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->exists();

            if ($alreadyFinished) {
                return redirect()->route('dashboard')->with('error', 'Anda sudah menyelesaikan absensi hari ini.');
            }

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
                'photo_path'        => $path,          // <--- INI SEKARANG URL CLOUDINARY
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
        $body = "{$user->name} dari Divisi " . ($user->division->name ?? 'N/A') . " mengajukan izin telat.";
        
        try {
            $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Error Late: ' . $e->getMessage());
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