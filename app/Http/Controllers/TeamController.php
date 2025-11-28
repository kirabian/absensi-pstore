<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $myId = $user->id;

        // 1. KUMPULKAN SEMUA ID CABANG MILIK USER LOGIN
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();

        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }

        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 2. QUERY USER LAIN (TIM)
        $query = User::where('users.id', '!=', $myId)
            ->where('users.is_active', true);

        if (empty($myBranchIds)) {
            $query->where('users.id', 0);
        } else {
            $query->where(function ($q) use ($myBranchIds) {
                $q->whereIn('users.branch_id', $myBranchIds)
                    ->orWhereHas('branches', function ($subQ) use ($myBranchIds) {
                        $subQ->whereIn('branches.id', $myBranchIds);
                    });
            });
        }

        // Ambil Data Tim
        $myTeam = $query->with([
            'attendances' => function ($q) {
                $q->whereDate('check_in_time', today());
            },
            'activeLateStatus',
            'divisions',
            'branch'
        ])
            ->join('branches', 'users.branch_id', '=', 'branches.id')
            ->orderBy('branches.name', 'asc')
            ->orderBy('users.name', 'asc')
            ->select('users.*')
            ->get();

        // 3. BARU: AMBIL DATA DETAIL CABANG UNTUK SECTION BAWAH
        // Kita ambil data cabang berdasarkan $myBranchIds dan hitung user aktifnya
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches'));
    }

    public function showBranch($id)
    {
        // 1. Ambil Data Cabang
        $branch = Branch::findOrFail($id);

        // Opsional: Cek hak akses
        $user = Auth::user();
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }
        
        if (!in_array($id, $myBranchIds) && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // 2. Ambil Karyawan di Cabang Tersebut
        $employees = User::where('branch_id', $id)
            ->where('is_active', true)
            ->with([
                'attendances' => function ($q) {
                    $q->whereDate('check_in_time', today());
                },
                'activeLateStatus',
                'division'
            ])
            ->orderBy('name', 'asc')
            ->get();

        return view('user_biasa.branch_detail', compact('branch', 'employees'));
    }

    public function myBranches()
    {
        $user = Auth::user();

        // Pastikan hanya audit yang bisa akses
        if ($user->role !== 'audit') {
            abort(403, 'Unauthorized action.');
        }

        $myId = $user->id;

        // 1. KUMPULKAN ID CABANG MILIK USER
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();

        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }

        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 2. AMBIL DATA CABANG YANG DIKONTROL
        $controlledBranches = Branch::whereIn('id', $myBranchIds)
            ->withCount(['users' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('user_biasa.my_branches', compact('controlledBranches'));
    }

    /**
     * Menampilkan riwayat absensi karyawan
     */
    public function showEmployeeHistory(Request $request, $branchId, $employeeId)
    {
        // Authorization check
        $user = Auth::user();
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }
        
        if (!in_array($branchId, $myBranchIds) && $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Get employee data
        $employee = User::findOrFail($employeeId);
        
        // Validate that employee belongs to the branch
        if ($employee->branch_id != $branchId) {
            abort(404, 'Employee not found in this branch.');
        }

        // Get filter parameters
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        // Query attendance history
        $history = Attendance::where('user_id', $employeeId)
            ->whereYear('check_in_time', $selectedYear)
            ->whereMonth('check_in_time', $selectedMonth)
            ->orderBy('check_in_time', 'desc')
            ->get();

        // Calculate summary
        $summary = [
            'hadir' => $history->where('status', 'verified')->count(),
            'telat' => $history->where('is_late_checkin', true)->count(),
            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),
            'pending' => $history->where('status', 'pending_verification')->count(),
        ];

        return view('attendance.history', compact(
            'history', 
            'summary', 
            'selectedMonth', 
            'selectedYear',
            'employee'
        ));
    }
}