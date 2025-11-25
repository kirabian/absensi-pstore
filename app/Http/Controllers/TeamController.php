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

        // 1. KUMPULKAN SEMUA CABANG MILIK USER LOGIN
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }
        
        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 2. QUERY USER LAIN (FIXED AMBIGUOUS COLUMN)
        // Perhatikan penambahan 'users.' di depan 'id' dan 'is_active'
        $query = User::where('users.id', '!=', $myId) 
                     ->where('users.is_active', true);

        if (empty($myBranchIds)) {
            $query->where('users.id', 0); // Force empty result
        } else {
            $query->where(function($q) use ($myBranchIds) {
                // Tambahkan 'users.' prefix juga disini untuk keamanan
                $q->whereIn('users.branch_id', $myBranchIds)
                  ->orWhereHas('branches', function($subQ) use ($myBranchIds) {
                      $subQ->whereIn('branches.id', $myBranchIds);
                  });
            });
        }

        // 3. EAGER LOAD DATA
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

        return view('user_biasa.team', compact('myTeam', 'myBranchIds'));
    }
}