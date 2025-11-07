<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Menampilkan daftar anggota tim satu divisi.
     */
    public function index()
    {
        // 1. Ambil ID divisi user yang sedang login
        $divisionId = Auth::user()->division_id;
        $myId = Auth::id();

        // 2. Cari semua user di divisi yang sama (kecuali diri sendiri)
        // 3. Eager load relasi 'attendances' TAPI HANYA untuk hari ini
        $myTeam = User::where('division_id', $divisionId)
            ->where('id', '!=', $myId)
            ->with(['attendances' => function ($query) {
                $query->whereDate('check_in_time', today());
            }])
            ->get();

        // 4. Kirim data ke view
        return view('user_biasa.team', compact('myTeam'));
    }
}
