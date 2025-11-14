<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendFcmNotification;

class SelfAttendanceController extends Controller
{
    use SendFcmNotification;

    public function create()
    {
        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('check_in_time', today())
            ->first();

        $activeLateStatus = LateNotification::where('user_id', Auth::id())
            ->where('is_active', true)
            ->whereDate('created_at', today())
            ->first();

        if ($todayAttendance) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah absen hari ini.');
        }

        if ($activeLateStatus) {
            return redirect()->route('dashboard')->with('error', 'Anda memiliki laporan telat aktif. Harap hapus laporan setelah tiba di kantor.');
        }

        return view('user_biasa.absen');
    }

    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = Auth::user();

        $alreadyAttended = Attendance::where('user_id', $user->id)->whereDate('check_in_time', today())->exists();
        $lateStatusActive = LateNotification::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyAttended || $lateStatusActive) {
            return redirect()->route('dashboard')->with('error', 'Gagal absen. Anda mungkin sudah absen atau laporan telat masih aktif.');
        }

        $path = $request->file('photo')->store('public/foto_mandiri');

        Attendance::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id, // <-- **INI PERBAIKAN PENTING #1**
            'check_in_time' => now(),
            'status' => 'pending_verification',
            'photo_path' => $path,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        $title = "Verifikasi Absensi";
        $body = $user->name . " telah absen mandiri dan perlu verifikasi.";
        $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);

        return redirect()->route('dashboard')->with('success', 'Berhasil absen mandiri. Menunggu verifikasi Audit.');
    }

    public function storeLateStatus(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        LateNotification::where('user_id', $user->id)->update(['is_active' => false]);

        LateNotification::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id, // <-- **INI PERBAIKAN PENTING #2**
            'message' => $request->message,
            'is_active' => true,
        ]);

        $title = "Izin Telat Masuk";
        $body = $user->name . " dari Divisi " . ($user->division->name ?? 'N/A') . " izin telat.";
        $this->sendNotificationToBranchRoles(['admin', 'audit'], $user->branch_id, $title, $body);

        return redirect()->route('dashboard')->with('success', 'Laporan telat terkirim.');
    }

    public function deleteLateStatus()
    {
        $notification = LateNotification::where('user_id', Auth::id())
            ->where('is_active', true)
            ->whereDate('created_at', today())
            ->first();

        if ($notification) {
            $notification->delete();
            return redirect()->route('dashboard')->with('success', 'Laporan telat dihapus. Anda sekarang bisa absen.');
        }

        return redirect()->route('dashboard')->with('error', 'Laporan telat tidak ditemukan.');
    }
}