<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\SendFcmNotification;
use Carbon\Carbon;

class AuditController extends Controller
{
    use SendFcmNotification;

    /**
     * Menampilkan daftar absensi mandiri yang butuh verifikasi (Status: Pending)
     */
    public function showVerificationList()
    {
        $user = Auth::user();
        
        // Query dasar: ambil yang status pending
        $query = Attendance::where('status', 'pending_verification')
            ->with('user.division');

        // --- LOGIKA HAK AKSES ---
        // Jika user adalah 'admin' atau 'audit', mereka bisa melihat SEMUA data (Universal Access).
        $isUniversalAccess = in_array($user->role, ['admin', 'audit']);

        // Jika BUKAN admin/audit, baru kita filter berdasarkan cabang dia (misal Supervisor)
        if (!$isUniversalAccess) {
            
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('users.branch_id', $myBranchIds);
                });
            } else {
                // User biasa tanpa cabang tidak boleh lihat apa-apa
                $query->where('id', 0);
            }
        }
        // Jika admin/audit, filter cabang diabaikan, jadi melihat SEMUA data.

        $pendingAttendances = $query->latest()->get();

        return view('audit.verification_list', compact('pendingAttendances'));
    }

    /**
     * Menyetujui Absensi Mandiri (Cara Lama / Quick Approve)
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
     * Menolak Absensi Mandiri (Hapus Data)
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
     * =========================================================================
     * FITUR BARU: VERIFIKASI DETAIL DENGAN BUKTI & STATUS KHUSUS
     * =========================================================================
     */
    public function verifyAttendance(Request $request, Attendance $attendance)
    {
        // 1. Validasi Input
        $request->validate([
            'presence_status' => 'required|string',
            'audit_photo' => 'nullable|image|max:5120', // Max 5MB
            'audit_note' => 'nullable|string'
        ]);

        $user = Auth::user();

        // 2. Logic Upload Foto Bukti (Jika ada)
        $auditPhotoPath = $attendance->audit_photo_path; // Default pakai yg lama jika tidak upload baru
        
        if ($request->hasFile('audit_photo')) {
            // Hapus foto lama jika ada
            if ($auditPhotoPath && Storage::disk('public')->exists($auditPhotoPath)) {
                Storage::disk('public')->delete($auditPhotoPath);
            }
            // Simpan foto baru
            $auditPhotoPath = $request->file('audit_photo')->store('audit-evidence', 'public');
        }

        // 3. Update Data Attendance
        $attendance->update([
            'status' => 'verified', // Status sistem jadi verified
            'presence_status' => $request->presence_status, // Status kehadiran spesifik (Masuk, Sakit, dll)
            'audit_photo_path' => $auditPhotoPath,
            'audit_note' => $request->audit_note,
            'verified_by_user_id' => $user->id, // Audit yang login
        ]);

        // Opsional: Kirim Notifikasi
        $title = "Absensi Diverifikasi Audit";
        $body = "Status kehadiran Anda tanggal " . $attendance->check_in_time->format('d/m/Y') . " telah diverifikasi menjadi: " . $request->presence_status;
        $this->sendNotificationToUser($attendance->user, $title, $body);

        return back()->with('success', 'Data absensi berhasil diverifikasi dan status diperbarui.');
    }

    /**
     * Menampilkan daftar izin telat
     */
    public function showLatePermissions()
    {
        $user = Auth::user();

        $query = LateNotification::where('is_active', true)
            ->with(['user', 'user.division']); 

        // --- LOGIKA HAK AKSES (Sama seperti Verification List) ---
        $isUniversalAccess = in_array($user->role, ['admin', 'audit']);

        if (!$isUniversalAccess) {
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

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

    // =========================================================================
    // FITUR: MISSED CHECKOUT (LUPA ABSEN PULANG)
    // =========================================================================

    /**
     * Menampilkan daftar karyawan yang sesi absensinya "Gantung" dari hari sebelumnya.
     * Kondisi: Check In Ada, Check Out NULL, Tanggal Check In < Hari Ini.
     */
    public function showMissedCheckouts()
    {
        $user = Auth::user();
        
        // Ambil data yang check_out-nya NULL DAN tanggal check_in-nya SEBELUM hari ini
        $query = Attendance::whereNull('check_out_time')
            ->whereDate('check_in_time', '<', today()) 
            ->with('user.division');

        // --- LOGIKA HAK AKSES (Konsisten: Admin/Audit lihat semua) ---
        $isUniversalAccess = in_array($user->role, ['admin', 'audit']);

        if (!$isUniversalAccess) {
            $pivotBranchIds = $user->branches->pluck('id')->toArray();
            $homebaseBranchId = $user->branch_id ? [$user->branch_id] : [];
            $myBranchIds = array_unique(array_merge($pivotBranchIds, $homebaseBranchId));

            if (!empty($myBranchIds)) {
                $query->whereHas('user', function ($q) use ($myBranchIds) {
                    $q->whereIn('users.branch_id', $myBranchIds);
                });
            } else {
                $query->where('id', 0);
            }
        }
        
        // Urutkan dari yang terlama gantungnya
        $missedCheckouts = $query->orderBy('check_in_time', 'asc')->get();

        return view('audit.missed_checkout_list', compact('missedCheckouts'));
    }

    /**
     * Proses Audit mengisikan jam pulang manual untuk karyawan yang lupa.
     */
    public function updateMissedCheckout(Request $request, $id)
    {
        $request->validate([
            'checkout_time' => 'required|date_format:H:i', // Audit input jam pulang (misal 17:00)
            'notes' => 'nullable|string'
        ]);

        $attendance = Attendance::findOrFail($id);
        
        // 1. Ambil tanggal dari Check In
        $checkInDate = Carbon::parse($attendance->check_in_time);
        
        // 2. Gabungkan tanggal Check In dengan Jam yang diinput Audit
        // Contoh: Check In tgl 20, Input 17:00 -> Hasil: 2023-11-20 17:00:00
        $checkOutDateTime = Carbon::parse($checkInDate->format('Y-m-d') . ' ' . $request->checkout_time);

        // 3. Logika Lembur Lintas Hari
        // Jika Jam Pulang yang diinput LEBIH KECIL dari Jam Masuk (Misal Masuk 08:00, Pulang 02:00)
        // Maka sistem menganggap itu adalah jam 02:00 BESOKNYA.
        if ($checkOutDateTime->lt($attendance->check_in_time)) {
            $checkOutDateTime->addDay();
        }

        // 4. Update Database
        $attendance->update([
            'check_out_time' => $checkOutDateTime,
            'status' => 'verified', // Karena diinput manual oleh Audit, anggap verified
            'verified_by_user_id' => Auth::id(),
            // Simpan catatan (Gabung dengan notes lama jika ada, atau buat baru)
            'audit_note' => 'Manual checkout by Audit: ' . $request->notes 
        ]);

        // 5. Kirim Notifikasi ke Karyawan
        $title = "Absen Pulang Diperbarui";
        $body = "Audit telah mengatur jam pulang Anda untuk tanggal " . $checkInDate->format('d/m/Y') . " menjadi jam " . $checkOutDateTime->format('H:i') . ".";
        $this->sendNotificationToUser($attendance->user, $title, $body);

        return back()->with('success', 'Absen pulang berhasil diperbarui manual. Sesi karyawan telah ditutup.');
    }
}