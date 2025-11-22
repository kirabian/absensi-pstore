<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $myId = $user->id;

        // Mulai Query, kecualikan diri sendiri
        $query = User::where('id', '!=', $myId);

        // --- LOGIKA UNTUK ROLE AUDIT (Berdasarkan Cabang) ---
        if ($user->role == 'audit') {
            // Ambil semua ID cabang dari pivot table (Multi Branch)
            $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
            
            // Tambahkan juga Homebase branch (jika ada)
            if ($user->branch_id) {
                $myBranchIds[] = $user->branch_id;
            }
            $myBranchIds = array_unique($myBranchIds); // Hapus duplikat

            // Cari user yang Homebase-nya ada di cabang tersebut
            // ATAU user yang punya akses Multi Branch ke cabang tersebut (sesama auditor)
            $query->where(function($q) use ($myBranchIds) {
                $q->whereIn('branch_id', $myBranchIds)
                  ->orWhereHas('branches', function($subQ) use ($myBranchIds) {
                      $subQ->whereIn('branches.id', $myBranchIds);
                  });
            });
        } 
        
        // --- LOGIKA UNTUK ROLE LAIN (Berdasarkan Divisi) ---
        else {
            // Ambil semua ID divisi dari pivot table (Multi Division)
            $myDivisionIds = $user->divisions()->pluck('divisions.id')->toArray();

            // Tambahkan Homebase division (jika ada, sebagai backup)
            if ($user->division_id) {
                $myDivisionIds[] = $user->division_id;
            }
            $myDivisionIds = array_unique($myDivisionIds);

            // Cari user yang memiliki SETIDAKNYA SATU divisi yang sama
            $query->whereHas('divisions', function($q) use ($myDivisionIds) {
                $q->whereIn('divisions.id', $myDivisionIds);
            });
        }

        // Eager Load Absensi & Data Pendukung
        $myTeam = $query->with([
                'attendances' => function ($q) {
                    $q->whereDate('check_in_time', today());
                },
                'activeLateStatus',
                'divisions', // Load relasi divisi untuk ditampilkan di view
                'branch'     // Load relasi cabang
            ])
            ->orderBy('name', 'asc') // Urutkan abjad biar rapi
            ->get();

        return view('user_biasa.team', compact('myTeam'));
    }
}