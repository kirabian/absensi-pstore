<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\LateNotification;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PDF; 

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];
        $branch_id = $user->branch_id;

        // =========================================================================
        // LOGIKA NOMOR ID CARD CUSTOM
        // Format: YY(Thn Masuk) MM(Bln Masuk) YY(Thn Lahir) [SPASI] MM(Bln Lahir) DD(Tgl Lahir) XXX(Urut ID)
        // Contoh: 250399 0512001
        // =========================================================================
        
        // 1. Ambil Data Tanggal Masuk (Hire Date)
        // Jika kosong, gunakan waktu sekarang
        $hireDate = $user->hire_date ? Carbon::parse($user->hire_date) : Carbon::now();
        
        // 2. Ambil Data Tanggal Lahir
        // NOTE: Karena kolom 'birth_date' belum ada di schema yang kamu kirim, 
        // saya beri default ke 1999-12-05 agar kode tidak error.
        // Jika kamu tambah kolom 'birth_date' di table users, ganti logika ini.
        $birthDate = $user->birth_date ? Carbon::parse($user->birth_date) : Carbon::parse('1999-05-12'); 

        // 3. Pecah Format
        $yyMasuk = $hireDate->format('y'); // 25 (2 digit tahun)
        $mmMasuk = $hireDate->format('m'); // 03 (2 digit bulan)
        
        $yyLahir = $birthDate->format('y'); // 99 (2 digit tahun)
        $mmLahir = $birthDate->format('m'); // 05 (2 digit bulan)
        $ddLahir = $birthDate->format('d'); // 12 (2 digit tanggal)
        
        // 4. Nomor Urut (Padding 3 digit, misal ID 1 jadi 001)
        $noUrut  = str_pad($user->id, 3, '0', STR_PAD_LEFT);

        // 5. Gabungkan String
        $data['idCardNumber'] = "{$yyMasuk}{$mmMasuk}{$yyLahir} {$mmLahir}{$ddLahir}{$noUrut}";
        
        // =========================================================================

        // 1. QUERY DASAR (Filter Cabang)
        $attendanceQuery = Attendance::query();
        $userQuery = User::query();
        $divisionQuery = Division::query();

        // Admin melihat semua jika tidak ada branch_id, role lain terkunci di branch
        if ($user->role != 'admin' || $branch_id != null) {
            $attendanceQuery->where('branch_id', $branch_id);
            $userQuery->where('branch_id', $branch_id);
            $divisionQuery->where('branch_id', $branch_id);
        }

        // 2. DATA IZIN & SESI HARI INI (Untuk Semua Role)
        $data['myLeaveToday'] = $this->getTodayLeaveRequest($user->id);
        $data = $this->getCommonDataForAllRoles($user, $data);

        // 3. HITUNG DATA PERSONAL (Wajib ada untuk fitur Absen Mandiri semua role)
        $data['myPendingCount'] = Attendance::where('user_id', $user->id)
            ->where('status', 'pending_verification')
            ->count();

        $data['myTeamCount'] = User::where('division_id', $user->division_id)
            ->where('id', '!=', $user->id)
            ->count();
            
        // Ambil statistik personal untuk tampilan ID Card (User biasa, Security, Audit butuh ini)
        $personalStats = $this->getUserAttendanceStats($user->id, $branch_id); 

        // ======================================================
        // 4. LOGIKA DASHBOARD PEKERJAAN (Dashboard Atas)
        // ======================================================

        if ($user->role == 'admin') {
            // --- ADMIN ---
            $data['totalUsers'] = $userQuery->where('role', '!=', 'admin')->count();
            $data['totalDivisions'] = $divisionQuery->count();
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
            
            // Chart Admin (Global)
            $data['stats'] = $this->getAdminAttendanceStats($branch_id); 
            
        } elseif ($user->role == 'audit') {
            // --- AUDIT ---
            $data['myTeamMembers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
            
            // Chart Audit (Fokus Verifikasi)
            $data['stats'] = $this->getAuditAttendanceStats($branch_id); 
            
        } elseif ($user->role == 'security') {
            // --- SECURITY ---
            $data['myScansToday'] = Attendance::where('scanned_by_user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->count();
            $data['totalUsers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();
            
            // Chart Security (Aktivitas Scan)
            $data['stats'] = $this->getSecurityAttendanceStats($user->id, $branch_id); 
            
        } else {
            // --- USER BIASA / LEADER ---
            // Chart Personal
            $data['stats'] = $personalStats;
        }

        // Kirim variable attendanceStats juga (Fallback compatibility)
        if (!isset($data['attendanceStats'])) {
            $data['attendanceStats'] = isset($data['stats']) ? $data['stats'] : $personalStats;
        }

        return view('dashboard', $data);
    }

    private function getTodayLeaveRequest($user_id)
    {
        return LeaveRequest::where('user_id', $user_id)
            ->where('is_active', true)
            ->where('status', 'approved')
            ->where(function($query) {
                $query->where(function($q) {
                    $q->whereIn('type', ['sakit', 'izin'])
                      ->whereDate('start_date', '<=', today())
                      ->whereDate('end_date', '>=', today());
                })->orWhere(function($q) {
                    $q->where('type', 'telat')
                      ->whereDate('start_date', today());
                });
            })
            ->first();
    }

    private function getCommonDataForAllRoles($user, $data)
    {
        // Cari Sesi Aktif (Check In ada, Check Out kosong) - Lookback 24 jam
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', Carbon::now()->subHours(24))
            ->latest('check_in_time')
            ->first();

        if ($activeSession) {
            $data['myAttendanceToday'] = $activeSession;
        } else {
            // Jika tidak ada sesi aktif, cari sesi yang SUDAH SELESAI hari ini
            $finishedSession = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->whereNotNull('check_out_time')
                ->latest('check_in_time')
                ->first();
            
            $data['myAttendanceToday'] = $finishedSession;
        }

        return $data;
    }

    private function getAdminAttendanceStats($branch_id = null)
    {
        $query = Attendance::whereDate('check_in_time', today());
        if ($branch_id) $query->where('branch_id', $branch_id);

        $totalUsers = User::when($branch_id, function($q) use ($branch_id) {
            return $q->where('branch_id', $branch_id);
        })->where('role', '!=', 'admin')->count();

        $presentCount = (clone $query)->count();
        $lateCount = (clone $query)->where('is_late_checkin', true)->count();
        $earlyCount = (clone $query)->where('is_early_checkout', true)->count();
        $pendingCount = (clone $query)->where('status', 'pending_verification')->count();
        $onTimeCount = max($presentCount - $lateCount, 0);
        $absentCount = max($totalUsers - $presentCount, 0);

        return [
            'total' => $presentCount, 'present' => $presentCount, 'late' => $lateCount, 'early' => $earlyCount,
            'pending' => $pendingCount, 'on_time' => $onTimeCount, 'absent' => $absentCount,
            'present_percentage' => $totalUsers > 0 ? round(($presentCount / $totalUsers) * 100) : 0,
            'late_percentage' => $presentCount > 0 ? round(($lateCount / $presentCount) * 100) : 0,
            'pending_percentage' => $presentCount > 0 ? round(($pendingCount / $presentCount) * 100) : 0,
            'absent_percentage' => $totalUsers > 0 ? round(($absentCount / $totalUsers) * 100) : 0,
        ];
    }

    private function getAuditAttendanceStats($branch_id = null)
    {
        $query = Attendance::whereDate('check_in_time', today());
        if ($branch_id) $query->where('branch_id', $branch_id);

        $totalToday = (clone $query)->count();
        $verified = (clone $query)->whereNotNull('verified_by_user_id')->count();
        $pending = (clone $query)->where('status', 'pending_verification')->count();
        $late = (clone $query)->where('is_late_checkin', true)->count();

        return [
            'total' => $totalToday, 'verified' => $verified, 'pending' => $pending, 'late' => $late,
            'verified_percentage' => $totalToday > 0 ? round(($verified / $totalToday) * 100) : 0,
            'pending_percentage' => $totalToday > 0 ? round(($pending / $totalToday) * 100) : 0,
            'late_percentage' => $totalToday > 0 ? round(($late / $totalToday) * 100) : 0,
        ];
    }

    private function getSecurityAttendanceStats($security_id, $branch_id = null)
    {
        $query = Attendance::whereDate('check_in_time', today());
        if ($branch_id) $query->where('branch_id', $branch_id);
        
        $scanQuery = (clone $query)->where('attendance_type', 'scan');
        $totalScans = (clone $scanQuery)->count(); 
        $checkInScans = (clone $scanQuery)->count(); 
        $checkOutScans = (clone $scanQuery)->whereNotNull('check_out_time')->count();

        return [
            'total_scans' => $checkInScans + $checkOutScans,
            'check_in_scans' => $checkInScans,
            'check_out_scans' => $checkOutScans,
            'check_in_percentage' => 100, 'check_out_percentage' => 100, 
        ];
    }

    private function getUserAttendanceStats($user_id, $branch_id = null)
    {
        $query = Attendance::where('user_id', $user_id)
            ->whereMonth('check_in_time', Carbon::now()->month)
            ->whereYear('check_in_time', Carbon::now()->year);
        
        if ($branch_id) $query->where('branch_id', $branch_id);

        $totalAttendances = (clone $query)->count();
        $late = (clone $query)->where('is_late_checkin', true)->count();
        $early = (clone $query)->where('is_early_checkout', true)->count();
        $pending = (clone $query)->where('status', 'pending_verification')->count();
        $onTime = max($totalAttendances - $late, 0);

        return [
            'total' => $totalAttendances, 'present' => $totalAttendances, 'late' => $late, 'early' => $early,
            'pending' => $pending, 'on_time' => $onTime,
            'present_percentage' => 100,
            'late_percentage' => $totalAttendances > 0 ? round(($late / $totalAttendances) * 100) : 0,
            'on_time_percentage' => $totalAttendances > 0 ? round(($onTime / $totalAttendances) * 100) : 0,
            'pending_percentage' => $totalAttendances > 0 ? round(($pending / $totalAttendances) * 100) : 0,
        ];
    }

    public function exportAttendancePDF(Request $request)
    {
        $user = Auth::user();
        $branch_id = $user->branch_id;
        $date = $request->get('date', today()->format('Y-m-d'));
        
        $data = [];
        $data['user'] = $user;
        $data['export_date'] = now()->format('d-m-Y H:i:s');
        $data['period'] = $date;
        
        switch ($user->role) {
            case 'admin':
                $data['stats'] = $this->getAdminAttendanceStats($branch_id);
                $data['title'] = 'Laporan Statistik Harian (Admin)';
                $data['role'] = 'Admin';
                break;
            case 'audit':
                $data['stats'] = $this->getAuditAttendanceStats($branch_id);
                $data['title'] = 'Laporan Verifikasi Absensi';
                $data['role'] = 'Audit';
                break;
            case 'security':
                $data['stats'] = $this->getSecurityAttendanceStats($user->id, $branch_id);
                $data['title'] = 'Laporan Aktivitas Security';
                $data['role'] = 'Security';
                break;
            default:
                $data['stats'] = $this->getUserAttendanceStats($user->id, $branch_id);
                $data['title'] = 'Laporan Absensi Personal (Bulan Ini)';
                $data['role'] = 'Karyawan';
                break;
        }

        $pdf = PDF::loadView('pdf.attendance-report', $data);
        return $pdf->download('laporan-absensi-' . $user->role . '-' . now()->format('Y-m-d') . '.pdf');
    }
}