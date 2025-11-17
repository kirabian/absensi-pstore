<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    /**
     * Constructor dengan middleware
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:security']);
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
        try {
            // 1. Validasi input
            $validated = $request->validate([
                'qr_code' => 'required|string|min:1'
            ]);

            $qrValue = $validated['qr_code'];
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

            // 4. Validasi Cabang
            if ($securityUser->branch_id && $userScanned->branch_id) {
                if ($securityUser->branch_id != $userScanned->branch_id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'GAGAL: Karyawan ini dari cabang berbeda (' . ($userScanned->branch->name ?? 'Pusat') . ').',
                    ], 403);
                }
            }

            // 5. Jika Validasi Sukses
            $photoUrl = $userScanned->profile_photo_path 
                ? asset('storage/' . $userScanned->profile_photo_path) 
                : 'https://ui-avatars.com/api/?name='.urlencode($userScanned->name).'&background=random';

            return response()->json([
                'status' => 'success',
                'message' => 'Scan Valid!',
                'data' => [
                    'name' => $userScanned->name,
                    'role' => ucfirst($userScanned->role),
                    'division' => $userScanned->division->name ?? '-',
                    'branch' => $userScanned->branch->name ?? 'Pusat',
                    'photo' => $photoUrl
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Scan Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }
}