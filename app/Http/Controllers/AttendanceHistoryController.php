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
        
        // Get filter parameters
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        // =================================================================
        // 1. AMBIL DATA ABSENSI ASLI (Scan, Selfie, dll)
        // =================================================================
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('check_in_time', $selectedYear)
            ->whereMonth('check_in_time', $selectedMonth)
            ->orderBy('check_in_time', 'desc')
            ->get();

        // =================================================================
        // 2. AMBIL DATA IZIN/CUTI/SAKIT (Approved)
        // =================================================================
        $leaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($q) use ($selectedMonth, $selectedYear) {
                // Logika agar izin yang lintas bulan tetap terambil
                $q->whereMonth('start_date', $selectedMonth)->whereYear('start_date', $selectedYear)
                  ->orWhere(function ($subQ) use ($selectedMonth, $selectedYear) {
                      $subQ->whereMonth('end_date', $selectedMonth)->whereYear('end_date', $selectedYear);
                  });
            })
            ->get();

        // =================================================================
        // 3. MERGE DATA (GABUNGKAN)
        // =================================================================
        // Kita gunakan Collection baru untuk menampung gabungan
        $historyCollection = $attendances;

        foreach ($leaves as $leave) {
            // Tentukan range tanggal izin (misal Sakit 3 hari)
            // Jika tipe 'telat', end_date null, anggap 1 hari
            $startDate = Carbon::parse($leave->start_date);
            $endDate = $leave->end_date ? Carbon::parse($leave->end_date) : $startDate;

            // Buat periode harian
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                // Hanya proses jika tanggalnya sesuai dengan Bulan & Tahun yang dipilih filter
                if ($date->month == $selectedMonth && $date->year == $selectedYear) {
                    
                    // Cek Conflict: Apakah di tanggal ini user sudah absen (Hadir)?
                    // Jika sudah ada absen real (misal dia Izin Telat tapi akhirnya masuk), 
                    // PRIORITASKAN data absensi asli (jangan ditimpa data izin).
                    $alreadyAttendance = $attendances->filter(function ($att) use ($date) {
                        return $att->check_in_time->isSameDay($date);
                    })->isNotEmpty();

                    if (!$alreadyAttendance) {
                        // BUAT OBJEK ATTENDANCE PALSU (Virtual) 
                        // Agar struktur datanya sama dengan tabel attendance di View Blade
                        $fakeAtt = new Attendance();
                        $fakeAtt->id = 'leave_' . $leave->id . '_' . $date->timestamp; // ID Dummy unik
                        $fakeAtt->user_id = $user->id;
                        $fakeAtt->check_in_time = $date->copy()->setTime(8, 0, 0); // Set jam default pagi
                        $fakeAtt->check_out_time = null; // Cuti/Sakit biasanya gak ada jam pulang
                        
                        // Mapping Status Text
                        $typeLabel = ucfirst($leave->type); 
                        if ($leave->type == 'telat') $typeLabel = 'Izin Telat';
                        if ($leave->type == 'wfh') $typeLabel = 'WFH';

                        $fakeAtt->presence_status = $typeLabel; // Sakit, Cuti, Izin, dll
                        $fakeAtt->status = 'verified'; // Karena sudah approved, anggap verified
                        $fakeAtt->attendance_type = 'leave'; // Penanda ini data cuti
                        $fakeAtt->is_late_checkin = false;
                        $fakeAtt->is_early_checkout = false;
                        $fakeAtt->photo_path = null;
                        $fakeAtt->photo_out_path = null;
                        $fakeAtt->audit_photo_path = null;
                        $fakeAtt->audit_note = "Pengajuan: " . $leave->reason; // Tampilkan alasan di note

                        // Masukkan ke koleksi utama
                        $historyCollection->push($fakeAtt);
                    }
                }
            }
        }

        // Urutkan ulang berdasarkan tanggal terbaru (Descending)
        $history = $historyCollection->sortByDesc('check_in_time');

        // =================================================================
        // 4. HITUNG SUMMARY (RECAP)
        // =================================================================
        // Hitung manual dari collection karena data campuran DB dan Virtual Object
        
        $summary = [
            'total' => $history->count(),
            
            // Hadir = Masuk, WFH, Izin Telat, Dinas
            'hadir' => $history->filter(function($item) {
                $s = strtolower($item->presence_status);
                return in_array($s, ['masuk', 'wfh', 'izin telat']) || str_contains($s, 'dinas');
            })->count(),

            // Telat = Ambil dari flag is_late_checkin
            'telat' => $history->where('is_late_checkin', true)->count(),

            // Pulang Cepat
            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),

            // Pending
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