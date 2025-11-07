<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendFcmNotification; // <-- 1. IMPORT TRAIT NOTIFIKASI

class SelfAttendanceController extends Controller
{
    use SendFcmNotification; // <-- 2. GUNAKAN TRAIT NOTIFIKASI

    /**
     * Menampilkan halaman form absen mandiri.
     */
    public function create()
    {
        // Cek apakah sudah absen hari ini
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('check_in_time', today())
            ->first();

        // Cek apakah ada laporan telat aktif
        $activeLateStatus = LateNotification::where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if ($todayAttendance) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah absen hari ini.');
        }

        if ($activeLateStatus) {
            return redirect()->route('dashboard')->with('error', 'Anda memiliki laporan telat aktif. Harap hapus laporan setelah tiba di kantor.');
        }

        return view('user_biasa.absen');
    }

    /**
     * Menyimpan data absen mandiri (foto + lokasi).
     */
    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = Auth::user(); // <-- Ambil data user untuk notif

        // Cek lagi jika sudah absen atau masih lapor telat
        $alreadyAttended = Attendance::where('user_id', $user->id)->whereDate('check_in_time', today())->exists();
        $lateStatusActive = LateNotification::where('user_id', $user->id)->where('is_active', true)->exists();

        if ($alreadyAttended || $lateStatusActive) {
            return redirect()->route('dashboard')->with('error', 'Gagal absen. Anda mungkin sudah absen atau laporan telat masih aktif.');
        }

        // Simpan foto ke 'storage/app/public/foto_mandiri'
        $path = $request->file('photo')->store('public/foto_mandiri');

        Attendance::create([
            'user_id' => $user->id,
            'check_in_time' => now(),
            'status' => 'pending_verification', // <-- Sesuai permintaan Anda
            'photo_path' => $path,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // --- 3. KIRIM NOTIFIKASI ---
        $title = "Verifikasi Absensi";
        $body = $user->name . " telah absen mandiri dan perlu verifikasi.";
        $this->sendNotificationToRoles(['admin', 'audit'], $title, $body);
        // -----------------------------

        return redirect()->route('dashboard')->with('success', 'Berhasil absen mandiri. Menunggu verifikasi Audit.');
    }

    /**
     * Menyimpan laporan telat.
     */
    public function storeLateStatus(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $user = Auth::user(); // <-- Ambil data user untuk notif

        // Nonaktifkan laporan lama jika ada
        LateNotification::where('user_id', $user->id)->update(['is_active' => false]);

        // Buat laporan baru
        LateNotification::create([
            'user_id' => $user->id,
            'message' => $request->message,
            'is_active' => true,
        ]);

        // --- 3. KIRIM NOTIFIKASI ---
        $title = "Izin Telat Masuk";
        $body = $user->name . " dari Divisi " . ($user->division->name ?? 'N/A') . " izin telat.";
        $this->sendNotificationToRoles(['admin', 'audit'], $title, $body);
        // -----------------------------

        return redirect()->route('dashboard')->with('success', 'Laporan telat terkirim. Anda bisa absen setelah menghapus laporan ini.');
    }

    /**
     * Menghapus laporan telat (saat tiba di kantor).
     */
    public function deleteLateStatus()
    {
        $notification = LateNotification::where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if ($notification) {
            $notification->delete(); // Hapus laporannya
            return redirect()->route('dashboard')->with('success', 'Laporan telat dihapus. Anda sekarang bisa absen.');
        }

        return redirect()->route('dashboard')->with('error', 'Laporan telat tidak ditemukan.');
    }
}
