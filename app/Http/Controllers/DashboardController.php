<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\LateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];
        $branch_id = $user->branch_id;

        // 1. QUERY DASAR (Filter Cabang)
        $attendanceQuery = Attendance::query();
        $userQuery = User::query();
        $divisionQuery = Division::query();
        $lateQuery = LateNotification::query();

        if ($user->role != 'admin' || $branch_id != null) {
            $attendanceQuery->where('branch_id', $branch_id);
            $userQuery->where('branch_id', $branch_id);
            $divisionQuery->where('branch_id', $branch_id);
            $lateQuery->where('branch_id', $branch_id);
        }

        // 2. ISI DATA UTAMA BERDASARKAN ROLE
        if ($user->role == 'admin') {
            $data['totalUsers'] = $userQuery->count();
            $data['totalDivisions'] = $divisionQuery->count();
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
        } elseif ($user->role == 'audit') {
            $data['myTeamMembers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();
            $data['pendingVerifications'] = $attendanceQuery->where('status', 'pending_verification')->count();
            $data['attendancesToday'] = $attendanceQuery->whereDate('check_in_time', today())->count();
        } elseif ($user->role == 'security') {
            $data['myScansToday'] = Attendance::where('scanned_by_user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->count();
            $data['totalUsers'] = $userQuery->whereIn('role', ['user_biasa', 'leader'])->count();
        } elseif ($user->role == 'user_biasa' || $user->role == 'leader') {
            // Data Absensi Pribadi
            $todayAttendances = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', today())
                ->orderBy('check_in_time', 'desc')
                ->get();

            $attendanceWithCheckout = $todayAttendances->first(fn ($att) => !is_null($att->check_out_time));
            
            if ($attendanceWithCheckout) {
                $data['myAttendanceToday'] = $attendanceWithCheckout;
            } else {
                $attendanceWithPhotoOut = $todayAttendances->first(fn ($att) => !is_null($att->photo_out_path));
                $data['myAttendanceToday'] = $attendanceWithPhotoOut ?? $todayAttendances->first();
            }

            $data['myPendingCount'] = Attendance::where('user_id', $user->id)
                ->where('status', 'pending_verification')
                ->count();

            $data['myTeamCount'] = User::where('division_id', $user->division_id)
                ->where('id', '!=', $user->id)
                ->count();

            $data['activeLateStatus'] = LateNotification::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('created_at', today())
                ->first();
        }

        // 3. DATA UNTUK GRAFIK (CHART) - 7 HARI TERAKHIR
        $chartData = $this->getChartData($user);
        $data['chartLabels'] = $chartData['labels'];
        $data['chartValues'] = $chartData['values'];
        $data['chartType'] = $chartData['type']; // 'team' atau 'personal'

        return view('dashboard', $data);
    }

    /**
     * Generate PDF Report
     */
    public function exportPdf()
    {
        $user = Auth::user();
        $branch_id = $user->branch_id;
        // Default: Bulan Ini
        $startDate = now()->startOfMonth();
        $endDate = now();

        // Query Data untuk PDF
        $query = Attendance::with(['user', 'user.division'])
            ->whereBetween('check_in_time', [$startDate, $endDate])
            ->orderBy('check_in_time', 'desc');

        // Filter Data PDF Sesuai Role
        if ($user->role == 'user_biasa') {
            $query->where('user_id', $user->id);
            $title = "Laporan Absensi Pribadi - " . $user->name;
        } elseif ($user->role == 'leader') {
            // Leader lihat divisi dia
            $query->whereHas('user', function($q) use ($user) {
                $q->where('division_id', $user->division_id);
            });
            $title = "Laporan Absensi Tim - " . ($user->division->name ?? 'Divisi');
        } elseif ($user->role == 'admin' || $user->role == 'audit') {
            // Admin/Audit lihat satu cabang
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
            $title = "Laporan Absensi Seluruh Karyawan";
        } elseif ($user->role == 'security') {
             // Security lihat log scan dia sendiri
             $query->where('scanned_by_user_id', $user->id);
             $title = "Laporan Riwayat Scan Security - " . $user->name;
        }

        $attendances = $query->get();

        $pdf = Pdf::loadView('reports.attendance_pdf', compact('attendances', 'title', 'startDate', 'endDate'));
        
        // Download file
        return $pdf->download('laporan_absensi_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Helper untuk data grafik
     */
    private function getChartData($user)
    {
        $labels = [];
        $values = [
            'present' => [],
            'late' => []
        ];
        
        // Ambil 7 hari terakhir
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            $dateStr = $date->format('Y-m-d');

            // Query Dasar berdasarkan Tanggal
            $query = Attendance::whereDate('check_in_time', $dateStr);

            // Filter Berdasarkan Role untuk Grafik
            if ($user->role == 'user_biasa') {
                // Grafik Kinerja Pribadi (Biner: 1 hadir, 0 tidak)
                $query->where('user_id', $user->id);
                $type = 'personal';
            } elseif ($user->role == 'leader') {
                // Grafik Divisi
                $query->whereHas('user', function($q) use ($user) {
                    $q->where('division_id', $user->division_id);
                });
                $type = 'team';
            } elseif ($user->role == 'admin' || $user->role == 'audit') {
                // Grafik Global/Cabang
                if ($user->branch_id) {
                    $query->where('branch_id', $user->branch_id);
                }
                $type = 'team';
            } else {
                // Security (Grafik Aktivitas Scan)
                $query->where('scanned_by_user_id', $user->id);
                $type = 'scan_activity';
            }

            // Hitung Data
            if ($type == 'scan_activity') {
                // Untuk security, hitung total scan
                $count = $query->count();
                $values['present'][] = $count; // Pakai slot 'present' untuk total scan
                $values['late'][] = 0;
            } else {
                // 1. Hadir Tepat Waktu
                $presentCount = (clone $query)->where('is_late_checkin', false)->count();
                // 2. Terlambat
                $lateCount = (clone $query)->where('is_late_checkin', true)->count();

                $values['present'][] = $presentCount;
                $values['late'][] = $lateCount;
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'type' => $type
        ];
    }
}