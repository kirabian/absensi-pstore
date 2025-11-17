<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendFcmNotification;

class ScanController extends Controller
{
    use SendFcmNotification;

    public function scanPage()
    {
        return view('security.scan'); // Pastikan nama view ini benar
    }

    public function getUserByQr(Request $request)
    {
        $request->validate(['qr_code' => 'required|string']);
        $security = Auth::user();

        $user = User::where('qr_code_value', $request->qr_code)
            ->where('branch_id', $security->branch_id)
            ->with('division')
            ->first();

        if ($user) {
            return response()->json([
                'user' => $user,
                'division_name' => $user->division->name ?? null
            ]);
        } else {
            return response()->json(['error' => 'User not found or not in this branch'], 404);
        }
    }

    public function storeAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('photo')->store('public/foto_security');
        $user = User::find($request->user_id);

        Attendance::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id, // <-- **INI PERBAIKAN PENTING #3**
            'check_in_time' => now(),
            'status' => 'verified',
            'photo_path' => $path,
            'scanned_by_user_id' => Auth::id(),
        ]);

        $title = "Absensi Masuk (Security)";
        $body = $user->name . " telah diabsen masuk oleh Security.";
        $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);

        return redirect()->route('security.scan')
            ->with('success', 'Absensi berhasil dicatat!');
    }
}
