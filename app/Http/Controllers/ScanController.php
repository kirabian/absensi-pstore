<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    /**
     * Middleware: HANYA izinkan Security.
     * Role lain (Admin, Audit, dll) akan ditolak.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            
            // LOGIKA BARU: Cek ketat hanya string 'security'
            if ($user->role !== 'security') {
                abort(403, 'Akses ditolak. Hanya Security yang boleh melakukan scan.');
            }
            
            return $next($request);
        });
    }

    /**
     * Menampilkan View Scanner (Kamera)
     */
    public function index()
    {
        return view('security.scan');
    }

    /**
     * Proses Validasi Data QR Code via AJAX
     */
    public function validateScan(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $qrValue = $request->qr_code;
        $securityUser = Auth::user(); // Pasti security karena sudah lolos middleware

        // 2. Cari User Karyawan berdasarkan QR Code
        $userScanned = User::with(['division', 'branch'])
                           ->where('qr_code_value', $qrValue)
                           ->first();

        // 3. Jika User Tidak Ditemukan
        if (!$userScanned) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR Code tidak terdaftar.'
            ], 404);
        }

        // 4. Validasi Cabang (Security Cabang A gaboleh scan Karyawan Cabang B)
        // Security pasti punya branch_id (sesuai logika store user Anda), tapi kita jaga-jaga cek null.
        if ($securityUser->branch_id != null) {
            if ($securityUser->branch_id != $userScanned->branch_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'GAGAL: Karyawan ini dari cabang berbeda (' . ($userScanned->branch->name ?? 'Pusat') . ').',
                ], 403);
            }
        }

        // 5. Jika Validasi Sukses
        // (Opsional) Insert ke tabel attendance di sini
        
        return response()->json([
            'status' => 'success',
            'message' => 'Scan Valid!',
            'data' => [
                'name' => $userScanned->name,
                'role' => ucfirst($userScanned->role),
                'division' => $userScanned->division->name ?? '-',
                'branch' => $userScanned->branch->name ?? 'Pusat',
                // Tampilkan foto jika ada, jika tidak pakai avatar default
                'photo' => $userScanned->profile_photo_path 
                            ? asset('storage/' . $userScanned->profile_photo_path) 
                            : 'https://ui-avatars.com/api/?name='.urlencode($userScanned->name).'&background=random'
            ]
        ]);
    }
}