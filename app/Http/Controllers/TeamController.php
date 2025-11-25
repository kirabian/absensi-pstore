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
        $query = User::where('id', '!=', $myId)
                     ->where('is_active', true); // Tambahan: Hanya tampilkan user aktif

        // --- LOGIKA UNTUK ROLE AUDIT (Tetap Multi Branch) ---
        if ($user->role == 'audit') {
            // Ambil semua ID cabang dari pivot table (Multi Branch)
            $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
            
            // Tambahkan juga Homebase branch (jika ada)
            if ($user->branch_id) {
                $myBranchIds[] = $user->branch_id;
            }
            $myBranchIds = array_unique($myBranchIds); // Hapus duplikat

            // Cari user yang Homebase-nya ada di cabang tersebut
            // ATAU user yang punya akses Multi Branch ke cabang tersebut
            $query->where(function($q) use ($myBranchIds) {
                $q->whereIn('branch_id', $myBranchIds)
                  ->orWhereHas('branches', function($subQ) use ($myBranchIds) {
                      $subQ->whereIn('branches.id', $myBranchIds);
                  });
            });
        } 
        
        // --- LOGIKA UNTUK ANGGOTA BIASA (HANYA CABANG YANG SAMA) ---
        else {
            // Filter hanya user yang memiliki branch_id SAMA dengan user yang sedang login
            if ($user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            } else {
                // Jika user yang login tidak punya cabang (error case), jangan tampilkan siapapun
                $query->where('id', 0);
            }
        }

        // Eager Load Absensi & Data Pendukung
        $myTeam = $query->with([
                'attendances' => function ($q) {
                    $q->whereDate('check_in_time', today());
                },
                'activeLateStatus',
                'divisions', 
                'branch'    
            ])
            ->orderBy('name', 'asc')
            ->get();

        return view('user_biasa.team', compact('myTeam'));
    }
}