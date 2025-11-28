<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            
            // --- PERBAIKAN DI SINI ---
            // Hitung Hadir: Status Verified TAPI BUKAN Alpha
            'hadir' => $history->where('status', 'verified')
                               ->where('presence_status', '!=', 'Alpha') 
                               ->count(),
            // --------------------------

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
}