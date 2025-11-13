<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AuditTeam;
use App\Models\LateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\SendFcmNotification;

class AuditController extends Controller
{
    use SendFcmNotification;

    /**
     * Menampilkan daftar absensi yang perlu verifikasi.
     */
    public function showVerificationList()
    {
        $user = Auth::user();
        $query = Attendance::where('status', 'pending_verification')
            ->with('user.division');

        // --- PERBAIKAN: Filter melalui relasi user ---
        if (($user->role == 'admin' && $user->branch_id != null) || $user->role == 'audit') {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }
        // Jika Super Admin (admin & branch_id == null), lihat semua tanpa filter

        $pendingAttendances = $query->latest()->get();

        // DEBUG: Untuk memastikan query bekerja
        // \Log::info('User: ' . $user->name . ', Role: ' . $user->role . ', Branch: ' . $user->branch_id);
        // \Log::info('Pending Count: ' . $pendingAttendances->count());

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

        $title = "Absensi Disetujui";
        $body = "Absen mandiri Anda pada " . $attendance->check_in_time->format('d/m/Y') . " telah disetujui.";
        $this->sendNotificationToUser($attendance->user, $title, $body);

        return back()->with('success', 'Absensi disetujui.');
    }

    /**
     * Menolak (reject) absensi mandiri.
     */
    public function reject(Attendance $attendance)
    {
        $user = $attendance->user;
        $date = $attendance->check_in_time->format('d/m/Y');

        if ($attendance->photo_path) {
            Storage::delete($attendance->photo_path);
        }
        $attendance->delete();

        $title = "Absensi Ditolak";
        $body = "Absen mandiri Anda pada " . $date . " ditolak oleh Audit.";
        $this->sendNotificationToUser($user, $title, $body);

        return back()->with('success', 'Absensi ditolak dan dihapus.');
    }


    /**
     * Menampilkan daftar user yang "Izin Telat Masuk".
     */
    public function showLatePermissions()
    {
        $user = Auth::user();

        $query = LateNotification::where('is_active', true)
            ->with(['user', 'user.division']); // Eager load user dan division user

        // Filter berdasarkan branch user melalui relasi
        if (($user->role == 'admin' && $user->branch_id != null) || $user->role == 'audit') {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        $latePermissions = $query->latest()->get();

        return view('audit.late_list', compact('latePermissions'));
    }
}
