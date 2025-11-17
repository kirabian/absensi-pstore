<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendFcmNotification;
use Illuminate\Support\Facades\Log;

class ScanController extends Controller
{
    use SendFcmNotification;

    public function scanPage()
    {
        return view('security.scan');
    }

    public function getUserByQr(Request $request)
    {
        try {
            $request->validate(['qr_code' => 'required|string']);
            $security = Auth::user();

            // Cek user berdasarkan QR dan Cabang Security
            $user = User::where('qr_code_value', $request->qr_code)
                ->where('branch_id', $security->branch_id) // Hanya bisa scan orang di cabang sendiri
                ->with('division')
                ->first();

            if ($user) {
                // Cek apakah user sudah absen hari ini (Opsional, biar tidak double)
                $alreadyAbsen = Attendance::where('user_id', $user->id)
                    ->whereDate('created_at', today())
                    ->exists();

                return response()->json([
                    'status' => 'success',
                    'user' => $user,
                    'division_name' => $user->division->name ?? '-',
                    'already_absen' => $alreadyAbsen
                ]);
            }

            return response()->json(['status' => 'error', 'message' => 'Karyawan tidak ditemukan atau beda cabang.'], 404);

        } catch (\Exception $e) {
            Log::error("Error Scan QR: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function storeAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            // Validasi photo wajib ada
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:4048',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            
            // Simpan Foto ke storage public
            $path = $request->file('photo')->store('foto_security', 'public');

            Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'check_in_time' => now(),
                'status' => 'verified',
                'photo_path' => $path,
                'scanned_by_user_id' => Auth::id(),
            ]);

            // Kirim Notifikasi
            $title = "Absensi Masuk (Security)";
            $body = $user->name . " telah diabsen masuk oleh Security.";
            // Gunakan try catch khusus notif agar jika notif gagal, absen tetap masuk
            try {
                $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);
            } catch (\Exception $ex) {
                // Silent fail untuk notifikasi
            }

            return redirect()->route('security.scan')
                ->with('success', 'Berhasil! Absensi ' . $user->name . ' tercatat.');

        } catch (\Exception $e) {
            return redirect()->route('security.scan')
                ->with('error', 'Gagal menyimpan absen: ' . $e->getMessage());
        }
    }
}