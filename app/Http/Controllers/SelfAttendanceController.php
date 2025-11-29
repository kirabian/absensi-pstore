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
        // 1. CEK APAKAH FILE DITERIMA SERVER ATAU TIDAK
        if (!$request->hasFile('photo')) {
            dd("STOP! Server tidak menerima file foto. Kemungkinan ukuran foto terlalu besar melebihi batas 'upload_max_filesize' di PHP Hosting Anda.");
        }

        // 2. CEK VALIDASI MANUAL (Pakai DD biar ketahuan salahnya)
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'photo' => 'required|image|max:10240', // 10MB
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            dd("STOP! Validasi Gagal:", $validator->errors()->all());
        }

        $user = Auth::user();
        $currentTime = now();

        // ==========================================================
        // TEST UPLOAD CLOUDINARY (DEBUG MODE)
        // ==========================================================
        try {
            $overlayPublicId = 'topeng_vader';
            $timestampText = $currentTime->locale('id')->translatedFormat('d M Y H:i');

            // Kita coba upload TANPA EFEK dulu untuk memastikan koneksi lancar
            // Kalau ini berhasil, berarti masalahnya di Transformation
            // Kalau ini gagal, berarti masalahnya di Kredensial .env

            $uploadedFile = Cloudinary::upload($request->file('photo')->getRealPath(), [
                'folder' => 'absensi_pstore_effects',
                'transformation' => [
                    [
                        'raw_transformation' => "l_$overlayPublicId/fl_layer_apply,fl_region_relative,g_faces,w_1.2,y_-0.05"
                    ],
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

            // Jika berhasil sampai sini, matikan dd di bawah dan lanjut
            // dd("BERHASIL UPLOAD! URL: " . $uploadedFile->getSecurePath());

            $path = $uploadedFile->getSecurePath();
        } catch (\Exception $e) {
            // TAMPILKAN ERROR CLOUDINARY DI LAYAR
            dd("STOP! Error Cloudinary:", $e->getMessage());
        }

        // ==============================================================
        // JIKA LOLOS, LANJUT PROSES DATABASE
        // ==============================================================

        $workSchedule = WorkSchedule::getScheduleForUser($user->id);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24))
            ->latest('check_in_time')
            ->first();

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
            $attendance->update([
                'check_out_time'    => $currentTime,
                'photo_out_path'    => $path,
                'is_early_checkout' => $isEarly,
            ]);
            $message = "Berhasil absen pulang (Debug Mode).";
        } else {
            $alreadyFinished = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->exists();
            if ($alreadyFinished) {
                return redirect()->route('dashboard')->with('error', 'Sudah selesai hari ini.');
            }
            $isLate = false;
            if ($workSchedule && $workSchedule->check_in_end) {
                $scheduleEnd = Carbon::parse($workSchedule->check_in_end);
                if (Carbon::parse($currentTime->format('H:i:s'))->gt($scheduleEnd)) {
                    $isLate = true;
                }
            }
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
            $message = 'Berhasil absen masuk (Debug Mode).';
        }

        return redirect()->route('dashboard')->with('success', $message)->with('photo_url', $path);
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
