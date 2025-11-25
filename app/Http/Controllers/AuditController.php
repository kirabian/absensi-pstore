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

    /**
     * Menampilkan daftar absensi yang perlu verifikasi.
     */
    public function showVerificationList()
    {
        $user = Auth::user();
        
        // 1. QUERY DASAR: Cari yang statusnya pending
        $query = Attendance::where('status', 'pending_verification')
            ->with('user.division');

        // 2. KUMPULKAN CABANG (Logic Multi-Branch)
        // Ambil dari Pivot Table (untuk Audit/Multi-branch)
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        
        // Ambil dari Homebase (Primary Branch) jika ada
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }
        
        // Hapus duplikat dan nilai kosong
        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 3. TERAPKAN FILTER
        // Logika:
        // - Jika Super Admin (Admin & Tidak punya cabang spesifik): Lihat SEMUA.
        // - Jika Audit ATAU Admin Cabang: Filter berdasarkan cabang yang mereka pegang.
        
        $isSuperAdmin = ($user->role == 'admin' && empty($myBranchIds));

        if (!$isSuperAdmin) {
            // Jika dia bukan super admin, WAJIB difilter berdasarkan cabang
            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('branch_id', $myBranchIds);
                });
            } else {
                // Safety: Jika role audit tapi belum diset cabang apapun, jangan tampilkan data (kosongkan)
                $query->where('id', 0);
            }
        }

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

        // Hapus foto jika ada sebelum hapus data
        if ($attendance->photo_path) {
            Storage::delete($attendance->photo_path);
        }
        
        // Force Delete karena ditolak (agar user bisa absen ulang jika perlu, atau dianggap tidak hadir)
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
            ->with(['user', 'user.division']); 

        // --- FILTER CABANG (Sama seperti Verification List) ---
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }
        $myBranchIds = array_filter(array_unique($myBranchIds));

        $isSuperAdmin = ($user->role == 'admin' && empty($myBranchIds));

        if (!$isSuperAdmin) {
            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('branch_id', $myBranchIds);
                });
            } else {
                $query->where('id', 0);
            }
        }

        $latePermissions = $query->latest()->get();

        return view('leave_requests.index', compact('latePermissions'));
    }
}