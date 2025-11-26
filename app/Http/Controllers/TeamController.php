<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch; // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            $query->where(function($q) use ($myBranchIds) {
                $q->whereIn('users.branch_id', $myBranchIds)
                  ->orWhereHas('branches', function($subQ) use ($myBranchIds) {
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
            ->withCount(['users' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('user_biasa.team', compact('myTeam', 'myBranchIds', 'controlledBranches'));
    }
}