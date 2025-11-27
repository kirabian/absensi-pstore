<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil Filter Bulan & Tahun dari Request, Default ke Bulan Ini
        $selectedMonth = $request->input('month', now()->month);
        $selectedYear = $request->input('year', now()->year);

        // 2. Query Data Absensi User Login berdasarkan Filter
        $history = Attendance::where('user_id', $user->id)
            ->whereMonth('check_in_time', $selectedMonth)
            ->whereYear('check_in_time', $selectedYear)
            ->orderBy('check_in_time', 'desc')
            ->get();

        // 3. Hitung Ringkasan Sederhana untuk Bulan Tersebut
        $summary = [
            'hadir' => $history->count(),
            'telat' => $history->where('is_late_checkin', true)->count(),
            'pulang_cepat' => $history->where('is_early_checkout', true)->count(),
            'pending' => $history->where('status', 'pending_verification')->count(),
        ];

        return view('attendance.history', compact('history', 'selectedMonth', 'selectedYear', 'summary'));
    }
}