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
        $this->middleware('auth');
        $this->middleware('role:security');
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
        $securityUser = Auth::user();

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
        if ($securityUser->branch_id != null) {
            if ($securityUser->branch_id != $userScanned->branch_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'GAGAL: Karyawan ini dari cabang berbeda (' . ($userScanned->branch->name ?? 'Pusat') . ').',
                ], 403);
            }
        }

        // 5. Jika Validasi Sukses
        return response()->json([
            'status' => 'success',
            'message' => 'Scan Valid!',
            'data' => [
                'name' => $userScanned->name,
                'role' => ucfirst($userScanned->role),
                'division' => $userScanned->division->name ?? '-',
                'branch' => $userScanned->branch->name ?? 'Pusat',
                'photo' => $userScanned->profile_photo_path 
                            ? asset('storage/' . $userScanned->profile_photo_path) 
                            : 'https://ui-avatars.com/api/?name='.urlencode($userScanned->name).'&background=random'
            ]
        ]);
    }
}