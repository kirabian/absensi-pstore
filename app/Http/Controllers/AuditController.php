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

        // PERBAIKAN DISINI: 
        // Izinkan 'admin' ATAU 'audit' menjadi Global Viewer jika mereka tidak terikat cabang spesifik
        $isGlobalViewer = (in_array($user->role, ['admin', 'audit']) && empty($myBranchIds));

        if (!$isGlobalViewer) {
            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('users.branch_id', $myBranchIds);
                });
            } else {
                // Jika user BUKAN admin/audit, dan TIDAK punya cabang, maka tidak lihat apa-apa.
                // Tapi karena logika $isGlobalViewer di atas sudah mencakup audit, 
                // maka audit tanpa cabang tidak akan masuk ke sini (mereka akan lihat semua).
                $query->where('id', 0);
            }
        }

        $pendingAttendances = $query->latest()->get();

        return view('audit.verification_list', compact('pendingAttendances'));
    }

    // ... (Method approve & reject tidak perlu diubah) ...
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

    public function showLatePermissions()
    {
        $user = Auth::user();

        $query = LateNotification::where('is_active', true)
            ->with(['user', 'user.division']); 

        // --- LOGIKA FILTER CABANG (Terapkan logika yang sama untuk Izin Telat) ---
        $pivotBranchIds = $user->branches->pluck('id')->toArray();
        $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
        $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

        // PERBAIKAN DISINI JUGA:
        $isGlobalViewer = (in_array($user->role, ['admin', 'audit']) && empty($myBranchIds));

        if (!$isGlobalViewer) {
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