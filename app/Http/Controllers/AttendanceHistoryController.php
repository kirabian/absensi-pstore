<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        // Query attendance history
        $history = Attendance::where('user_id', $user->id)
            ->whereYear('check_in_time', $selectedYear)
            ->whereMonth('check_in_time', $selectedMonth)
            ->orderBy('check_in_time', 'desc')
            ->get();

        // Calculate summary
        $summary = [
            'total' => $history->count(),
            'hadir' => $history->where('status', 'verified')
                               ->where('presence_status', '!=', 'Alpha') 
                               ->count(),
            'telat' => $history->where('is_late_checkin', true)->count(),
            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),
            'pending' => $history->where('status', 'pending_verification')->count(),
        ];

        return view('attendance.history', compact(
            'history', 
            'summary', 
            'selectedMonth', 
            'selectedYear'
        ));
    }

    // --- METODE BARU UNTUK AUDIT EDIT DATA ---
    public function updateByAudit(Request $request, $id)
    {
        // 1. Validasi Role
        if (Auth::user()->role !== 'audit') {
            abort(403, 'Akses Ditolak. Hanya Audit yang boleh mengedit data.');
        }

        $request->validate([
            'presence_status' => 'required|string',
            'check_in_time'   => 'required', // Format H:i
            'check_out_time'  => 'nullable', // Format H:i
            'status'          => 'required|in:verified,pending_verification,rejected',
            'audit_note'      => 'nullable|string',
            'audit_photo'     => 'nullable|image|max:2048' // Validasi Foto (Max 2MB)
        ]);

        $attendance = Attendance::findOrFail($id);

        // 2. Olah Waktu
        $originalDate = $attendance->check_in_time->format('Y-m-d');
        $newCheckIn = Carbon::parse($originalDate . ' ' . $request->check_in_time);
        $newCheckOut = $request->check_out_time ? Carbon::parse($originalDate . ' ' . $request->check_out_time) : null;

        // 3. Cek Keterlambatan Ulang
        $workSchedule = WorkSchedule::getScheduleForUser($attendance->user_id);
        $isLate = $attendance->is_late_checkin;
        
        if ($workSchedule && $request->presence_status == 'Masuk') {
            $scheduleStart = Carbon::parse($originalDate . ' ' . $workSchedule->check_in_end);
            $isLate = $newCheckIn->gt($scheduleStart);
        }

        // 4. Proses Upload Foto Audit (Jika Ada)
        $auditPhotoPath = $attendance->audit_photo_path; // Default pakai yg lama
        if ($request->hasFile('audit_photo')) {
            // Hapus file lama jika ada (opsional, biar hemat storage)
            // if ($auditPhotoPath && Storage::exists($auditPhotoPath)) Storage::delete($auditPhotoPath);
            
            $auditPhotoPath = $request->file('audit_photo')->store('audit-proofs', 'public');
        }

        // 5. Update Data
        $attendance->update([
            'presence_status'     => $request->presence_status,
            'check_in_time'       => $newCheckIn,
            'check_out_time'      => $newCheckOut,
            'status'              => $request->status,
            'is_late_checkin'     => $isLate,
            'audit_note'          => $request->audit_note,
            'audit_photo_path'    => $auditPhotoPath, // Simpan path foto baru
            'verified_by_user_id' => ($request->status == 'verified') ? Auth::id() : null,
            'attendance_type'     => ($attendance->presence_status == 'Alpha' && $request->presence_status != 'Alpha') ? 'manual' : $attendance->attendance_type,
        ]);

        return back()->with('success', 'Data absensi berhasil diperbarui oleh Audit.');
    }
}