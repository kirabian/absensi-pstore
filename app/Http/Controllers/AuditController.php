<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\SendFcmNotification;

class AuditController extends Controller
{
    use SendFcmNotification;

    public function showVerificationList()
    {
        $user = Auth::user();
        
        $query = Attendance::where('status', 'pending_verification')
            ->with('user.division');

        // --- LOGIKA FILTER CABANG ---
        $pivotBranchIds = $user->branches->pluck('id')->toArray();
        $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
        $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

        $isSuperAdmin = ($user->role == 'admin' && empty($myBranchIds));

        if (!$isSuperAdmin) {
            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    // Gunakan prefix 'users.' agar aman
                    $q->whereIn('users.branch_id', $myBranchIds);
                });
            } else {
                // Jika audit tidak punya cabang, jangan tampilkan data user lain
                $query->where('id', 0);
            }
        }

        $pendingAttendances = $query->latest()->get();

        return view('audit.verification_list', compact('pendingAttendances'));
    }

    // ... (Method approve & reject TETAP SAMA) ...
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
     * Menampilkan daftar user yang "Izin Telat Masuk" (Dari LateNotification / Mobile Realtime).
     */
    public function showLatePermissions()
    {
        $user = Auth::user();

        $query = LateNotification::where('is_active', true)
            ->with(['user', 'user.division']); 

        // --- LOGIKA FILTER CABANG ---
        $pivotBranchIds = $user->branches->pluck('id')->toArray();
        $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
        $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

        $isSuperAdmin = ($user->role == 'admin' && empty($myBranchIds));

        if (!$isSuperAdmin) {
            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('users.branch_id', $myBranchIds);
                });
            } else {
                $query->where('id', 0);
            }
        }

        $latePermissions = $query->latest()->get();

        return view('leave_requests.index', compact('latePermissions'));
    }
}