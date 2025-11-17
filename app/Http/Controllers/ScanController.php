<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:security']);
    }

    /**
     * Menampilkan View Scanner
     */
    public function index()
    {
        \Illuminate\Support\Facades\Log::info('ScanController accessed by user: ' . Auth::id());
        return view('security.scan');
    }

    /**
     * Proses Validasi QR Code
     */
    public function validateScan(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Validate scan request', ['user' => Auth::id(), 'data' => $request->all()]);

        // Validasi input
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $qrValue = $request->qr_code;
        $securityUser = Auth::user();

        // Cari user berdasarkan QR code
        $userScanned = User::with(['division', 'branch'])
                           ->where('qr_code_value', $qrValue)
                           ->first();

        if (!$userScanned) {
            \Illuminate\Support\Facades\Log::warning('QR code not found: ' . $qrValue);
            return response()->json([
                'status' => 'error',
                'message' => 'QR Code tidak terdaftar.'
            ], 404);
        }

        // Validasi cabang
        if ($securityUser->branch_id && $userScanned->branch_id && 
            $securityUser->branch_id != $userScanned->branch_id) {
            \Illuminate\Support\Facades\Log::warning('Branch mismatch', [
                'security_branch' => $securityUser->branch_id,
                'user_branch' => $userScanned->branch_id
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'GAGAL: Karyawan dari cabang berbeda (' . ($userScanned->branch->name ?? 'Pusat') . ').'
            ], 403);
        }

        // Response sukses
        \Illuminate\Support\Facades\Log::info('Scan successful', ['scanned_user' => $userScanned->id]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Scan Valid!',
            'data' => [
                'name' => $userScanned->name,
                'role' => $userScanned->role,
                'division' => $userScanned->division->name ?? '-',
                'branch' => $userScanned->branch->name ?? 'Pusat',
                'photo' => $userScanned->profile_photo_path 
                            ? asset('storage/' . $userScanned->profile_photo_path) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($userScanned->name) . '&background=random'
            ]
        ]);
    }
}