<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    // MENAMPILKAN LIST DATA
    public function index()
    {
        $user = Auth::user();
        $query = LeaveRequest::with('user')->latest();

        // Logika Filter Role
        if ($user->role == 'admin') {
            // Admin lihat semua
        } elseif ($user->role == 'audit') {
            // Audit lihat tim satu cabang
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        } else {
            // User biasa lihat punya sendiri
            $query->where('user_id', $user->id);
        }

        $requests = $query->paginate(10);
        return view('leave_requests.index', compact('requests'));
    }

    // MENAMPILKAN FORM
    public function create()
    {
        return view('leave_requests.create');
    }

    // MENYIMPAN DATA (User Submit)
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sakit,izin,telat',
            'reason' => 'required|string|max:255',
            'file_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'start_date' => 'required|date',
            'end_date'   => 'required_if:type,sakit,izin|nullable|date|after_or_equal:start_date',
            'start_time' => 'required_if:type,telat|nullable|date_format:H:i',
        ], [
            'file_proof.required' => 'Bukti foto wajib diupload.',
            'start_time.required_if' => 'Jam kedatangan wajib diisi jika telat.',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'type' => $request->type,
            'reason' => $request->reason,
            'start_date' => $request->start_date,
            'status' => 'pending',
            'is_active' => true,
        ];

        // Logika pemisahan input
        if ($request->type === 'telat') {
            $data['start_time'] = $request->start_time;
            $data['end_date'] = null;
        } else {
            $data['end_date'] = $request->end_date;
            $data['start_time'] = null;
        }

        // Upload File
        if ($request->hasFile('file_proof')) {
            $path = $request->file('file_proof')->store('proofs', 'public');
            $data['file_proof'] = $path;
        }

        LeaveRequest::create($data);

        return redirect()->route('leave-requests.index')->with('success', 'Pengajuan berhasil dikirim.');
    }

    // ACTION: USER BATALKAN / SAMPAI KANTOR
    public function cancel(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id != Auth::id()) {
            abort(403);
        }

        // Update jadi cancelled & non-aktif
        $leaveRequest->update([
            'status' => 'cancelled',
            'is_active' => false
        ]);

        $msg = $leaveRequest->type == 'telat' ? 'Izin telat dibatalkan. Silakan lakukan absensi.' : 'Pengajuan izin dibatalkan.';
        return redirect()->route('dashboard')->with('success', $msg);
    }

    // ACTION: APPROVE (Admin/Audit)
    public function approve(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Pengajuan disetujui.');
    }

    // ACTION: REJECT (Admin/Audit)
    public function reject(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update(['status' => 'rejected', 'is_active' => false]);
        return redirect()->back()->with('success', 'Pengajuan ditolak.');
    }
}
