<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        $divisionId = Auth::user()->division_id;
        $myId = Auth::id();

        // 2. Eager load absensi HARI INI
        // 3. Eager load izin telat HARI INI (BARU)
        $myTeam = User::where('division_id', $divisionId)
            ->where('id', '!=', $myId)
            ->with([
                'attendances' => function ($query) {
                    $query->whereDate('check_in_time', today());
                },
                'activeLateStatus' // <-- PANGGIL RELASI BARU
            ])
            ->get();

        return view('user_biasa.team', compact('myTeam'));
    }
}
