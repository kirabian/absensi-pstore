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
        // Ambil dari Pivot Table (Multi Branch)
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        
        // Ambil dari Homebase (Primary Branch) jika ada, lalu gabungkan
        if ($user->branch_id) {
            $myBranchIds[] = $user->branch_id;
        }
        
        // Hapus duplikat (misal di pivot ada, di homebase ada) dan filter null/kosong
        $myBranchIds = array_filter(array_unique($myBranchIds));

        // 2. QUERY USER LAIN
        // Kecualikan diri sendiri & hanya user aktif
        $query = User::where('id', '!=', $myId)
                     ->where('is_active', true);

        // Jika user tidak punya cabang sama sekali, jangan tampilkan siapapun
        if (empty($myBranchIds)) {
            $query->where('id', 0); // Force empty result
        } else {
            // Tampilkan user yang:
            $query->where(function($q) use ($myBranchIds) {
                // A. Homebase-nya ada di salah satu cabang yang saya pegang
                $q->whereIn('branch_id', $myBranchIds)
                
                // B. ATAU user tersebut juga memegang akses (multi-branch) ke salah satu cabang saya
                // (Ini opsional: aktifkan jika User A (Cabang 1) harus melihat User B (Cabang 2) 
                // jika User B punya akses tambahan ke Cabang 1)
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
            // Urutkan berdasarkan Nama Cabang dulu, baru Nama Orang (biar rapih terkelompok)
            ->join('branches', 'users.branch_id', '=', 'branches.id')
            ->orderBy('branches.name', 'asc') 
            ->orderBy('users.name', 'asc')
            ->select('users.*') // Pastikan hanya select kolom user agar tidak bentrok dengan join
            ->get();

        return view('user_biasa.team', compact('myTeam', 'myBranchIds'));
    }
}