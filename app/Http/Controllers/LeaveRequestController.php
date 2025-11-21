<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\LeaveRequest;
use App\Models\LateNotification;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function create()
    {
        return view('leave.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'type' => 'required|in:sakit,telat,cuti,libur_mingguan',
            'start_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|min:10',
            'file_proof' => 'nullable|image|max:51200',
        ];

        if ($request->type == 'sakit' || $request->type == 'telat') {
            $rules['file_proof'] = 'required|image|max:51200';
        }

        if ($request->type == 'cuti' || $request->type == 'sakit') {
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        } else {
            $rules['end_date'] = 'nullable|date';
        }

        $validated = $request->validate($rules, [
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
            'file_proof.required' => 'Bukti foto wajib diisi untuk izin Sakit atau Telat.',
            'end_date.required' => 'Tanggal selesai wajib diisi untuk Cuti atau Sakit.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.'
        ]);

        $userId = Auth::id();

        // Cek apakah sudah ada izin telat aktif untuk hari ini
        if ($validated['type'] == 'telat') {
            $existingLatePermission = LeaveRequest::where('user_id', $userId)
                ->where('type', 'telat')
                ->where('is_active', true)
                ->whereDate('start_date', $validated['start_date'])
                ->first();

            if ($existingLatePermission) {
                return back()->with('error', 'Anda sudah memiliki izin telat aktif untuk tanggal ini.')->withInput();
            }
        }

        // Logika untuk Libur Mingguan
        if ($validated['type'] == 'libur_mingguan') {
            $startDate = Carbon::parse($validated['start_date']);
            $weekStartDate = $startDate->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
            $weekEndDate = $startDate->endOfWeek(Carbon::SATURDAY)->format('Y-m-d');

            $existingRequest = LeaveRequest::where('user_id', $userId)
                ->where('type', 'libur_mingguan')
                ->where('status', '!=', 'rejected')
                ->whereBetween('start_date', [$weekStartDate, $weekEndDate])
                ->first();

            if ($existingRequest) {
                return back()->with('error', 'Anda sudah mengajukan libur mingguan untuk minggu ini.')->withInput();
            }

            $validated['end_date'] = $validated['start_date'];
        }

        // Set end_date untuk telat
        if ($validated['type'] == 'telat') {
            $validated['end_date'] = $validated['start_date'];
        }

        // Handle Upload File
        $filePath = null;
        if ($request->hasFile('file_proof')) {
            $filePath = $request->file('file_proof')->store('proofs', 'public');
        }

        // Simpan ke Database dengan is_active true untuk telat
        $leaveRequest = LeaveRequest::create([
            'user_id' => $userId,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? $validated['start_date'],
            'reason' => $validated['reason'],
            'file_proof' => $filePath,
            'status' => 'pending',
            'is_active' => $validated['type'] == 'telat', // Aktif hanya untuk telat
        ]);

        // Buat LateNotification untuk izin telat
        if ($validated['type'] == 'telat') {
            LateNotification::create([
                'user_id' => $userId,
                'leave_request_id' => $leaveRequest->id, // Link ke leave request
                'message' => $validated['reason'],
                'is_active' => true,
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Pengajuan berhasil dikirim dan menunggu persetujuan.');
    }

    // Method baru untuk membatalkan izin telat
    public function cancelLatePermission($id)
    {
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('type', 'telat')
            ->where('is_active', true)
            ->firstOrFail();

        // Nonaktifkan leave request
        $leaveRequest->update([
            'is_active' => false,
            'status' => 'cancelled'
        ]);

        // Nonaktifkan late notification
        LateNotification::where('leave_request_id', $id)->update(['is_active' => false]);

        return redirect()->route('dashboard')->with('success', 'Izin telat berhasil dibatalkan.');
    }

    // Method untuk melihat daftar izin telat aktif (untuk admin/audit)
    public function activeLatePermissions()
    {
        $latePermissions = LeaveRequest::with(['user', 'user.division'])
            ->where('type', 'telat')
            ->where('is_active', true)
            ->where('status', 'approved')
            ->whereDate('start_date', today())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('audit.active-late-permissions', compact('latePermissions'));
    }
}