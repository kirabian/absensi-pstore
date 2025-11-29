<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\WorkSchedule;
use App\Models\LeaveRequest; // <--- Tambahan Wajib
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod; // <--- Tambahan Wajib untuk loop tanggal

class AttendanceHistoryController extends Controller
{
   public function index(Request $request)
    {
        $user = Auth::user();
        
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        // 1. AMBIL DATA ABSENSI REAL
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('check_in_time', $selectedYear)
            ->whereMonth('check_in_time', $selectedMonth)
            ->orderBy('check_in_time', 'desc')
            ->get();

        // 2. AMBIL DATA IZIN (Approved)
        $leaves = \App\Models\LeaveRequest::where('user_id', $user->id) // Pastikan Model diload
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($q) use ($selectedMonth, $selectedYear) {
                $q->whereMonth('start_date', $selectedMonth)->whereYear('start_date', $selectedYear)
                  ->orWhere(function ($subQ) use ($selectedMonth, $selectedYear) {
                      $subQ->whereMonth('end_date', $selectedMonth)->whereYear('end_date', $selectedYear);
                  });
            })
            ->get();

        // 3. MERGE DATA
        $historyCollection = $attendances;

        foreach ($leaves as $leave) {
            $startDate = \Carbon\Carbon::parse($leave->start_date);
            $endDate = $leave->end_date ? \Carbon\Carbon::parse($leave->end_date) : $startDate;
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                if ($date->month == $selectedMonth && $date->year == $selectedYear) {
                    // Cek duplikasi dengan absen real
                    $alreadyAttendance = $attendances->filter(function ($att) use ($date) {
                        return $att->check_in_time->isSameDay($date);
                    })->isNotEmpty();

                    if (!$alreadyAttendance) {
                        $fakeAtt = new Attendance();
                        $fakeAtt->id = 'leave_' . $leave->id . '_' . $date->timestamp;
                        $fakeAtt->user_id = $user->id;
                        $fakeAtt->check_in_time = $date->copy()->setTime(8, 0, 0);
                        $fakeAtt->check_out_time = null;
                        
                        $typeLabel = ucfirst($leave->type); 
                        if ($leave->type == 'telat') $typeLabel = 'Izin Telat';
                        if ($leave->type == 'wfh') $typeLabel = 'WFH';

                        $fakeAtt->presence_status = $typeLabel;
                        $fakeAtt->status = 'verified'; 
                        $fakeAtt->attendance_type = 'leave';
                        $fakeAtt->is_late_checkin = false;
                        $fakeAtt->is_early_checkout = false;
                        $fakeAtt->audit_note = "Pengajuan: " . $leave->reason;

                        $historyCollection->push($fakeAtt);
                    }
                }
            }
        }

        $history = $historyCollection->sortByDesc('check_in_time');

        // 4. HITUNG SUMMARY YANG LEBIH AKURAT
        $summary = [
            'total' => $history->count(),
            
            // Hadir = Masuk, WFH, Dinas, Izin Telat
            'hadir' => $history->filter(function($i) {
                $s = strtolower($i->presence_status ?? '');
                return in_array($s, ['masuk', 'wfh', 'izin telat']) || str_contains($s, 'dinas');
            })->count(),

            // Sakit
            'sakit' => $history->filter(function($i) {
                return strtolower($i->presence_status ?? '') === 'sakit';
            })->count(),

            // Izin / Cuti
            'izin' => $history->filter(function($i) {
                return in_array(strtolower($i->presence_status ?? ''), ['izin', 'cuti']);
            })->count(),

            // Alpha (Benar-benar tidak hadir tanpa keterangan)
            'alpha' => $history->filter(function($i) {
                return strtolower($i->presence_status ?? '') === 'alpha';
            })->count(),

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

    // --- METODE BARU UNTUK AUDIT EDIT DATA (TETAP SEPERTI KODE KAMU) ---
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