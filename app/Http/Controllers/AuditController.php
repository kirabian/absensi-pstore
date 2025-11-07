<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AuditTeam;
use App\Models\LateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\SendFcmNotification; // <-- 1. IMPORT TRAIT NOTIFIKASI

class AuditController extends Controller
{
    use SendFcmNotification; // <-- 2. GUNAKAN TRAIT NOTIFIKASI

    /**
     * Menampilkan daftar absensi yang perlu verifikasi.
     * (Foto + Lokasi)
     */
    public function showVerificationList()
    {
        $user = Auth::user();
        $query = Attendance::where('status', 'pending_verification')->with('user.division');

        // Jika yang login adalah AUDIT, filter hanya tim-nya
        if ($user->role == 'audit') {
            // 1. Ambil ID divisi yang dia audit
            $myDivisionIds = AuditTeam::where('user_id', $user->id)->pluck('division_id');
            // 2. Ambil ID user yang ada di divisi tersebut
            $myUserIds = User::whereIn('division_id', $myDivisionIds)->pluck('id');

            // Filter query absensi
            $query->whereIn('user_id', $myUserIds);
        }
        // Jika ADMIN, dia bisa lihat semua (tanpa filter)

        $pendingAttendances = $query->latest()->get();

        return view('audit.verification_list', compact('pendingAttendances'));
    }

    /**
     * Menyetujui (approve) absensi mandiri.
     */
    public function approve(Attendance $attendance)
    {
        $attendance->update([
            'status' => 'verified',
            'verified_by_user_id' => Auth::id()
        ]);

        // --- 3. KIRIM NOTIFIKASI (ke 1 user) ---
        $title = "Absensi Disetujui";
        $body = "Absen mandiri Anda pada " . $attendance->check_in_time->format('d/m/Y') . " telah disetujui.";
        // $attendance->user adalah relasi ke model User
        $this->sendNotificationToUser($attendance->user, $title, $body);
        // ------------------------------------

        return back()->with('success', 'Absensi disetujui.');
    }

    /**
     * Menolak (reject) absensi mandiri.
     */
    public function reject(Attendance $attendance)
    {
        // Ambil data user & tanggal SEBELUM dihapus
        $user = $attendance->user;
        $date = $attendance->check_in_time->format('d/m/Y');

        // 1. Hapus fotonya dari storage
        if ($attendance->photo_path) {
            Storage::delete($attendance->photo_path);
        }

        // 2. Hapus datanya dari database
        $attendance->delete();

        // --- 3. KIRIM NOTIFIKASI (ke 1 user) ---
        $title = "Absensi Ditolak";
        $body = "Absen mandiri Anda pada " . $date . " ditolak oleh Audit.";
        $this->sendNotificationToUser($user, $title, $body);
        // ------------------------------------

        return back()->with('success', 'Absensi ditolak dan dihapus.');
    }


    /**
     * Menampilkan daftar user yang "Izin Telat Masuk".
     */
    public function showLatePermissions()
    {
        $user = Auth::user();
        $query = LateNotification::where('is_active', true)->with('user.division');

        // Jika yang login adalah AUDIT, filter hanya tim-nya
        if ($user->role == 'audit') {
            $myDivisionIds = AuditTeam::where('user_id', $user->id)->pluck('division_id');
            $myUserIds = User::whereIn('division_id', $myDivisionIds)->pluck('id');
            $query->whereIn('user_id', $myUserIds);
        }

        $latePermissions = $query->latest()->get();

        return view('audit.late_list', compact('latePermissions'));
    }
}
