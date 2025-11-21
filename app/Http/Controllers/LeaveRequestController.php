<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    // 1. TAMPILAN LIST / MONITORING
    public function index()
    {
        $user = Auth::user();
        $query = LeaveRequest::with('user')->latest();

        // LOGIKA FILTER BERDASARKAN ROLE
        if ($user->role == 'admin') {
            // Admin melihat SEMUA data
        } elseif ($user->role == 'audit') {
            // Audit melihat HANYA TIMNYA (Satu Divisi atau Satu Cabang)
            // Asumsi: Audit melihat user di cabang yang sama
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id); 
            });
        } else {
            // User Biasa / Leader hanya melihat punya SENDIRI
            $query->where('user_id', $user->id);
        }

        $requests = $query->paginate(10);

        return view('leave_requests.index', compact('requests'));
    }

    // 2. FORM CREATE (Sudah ada sebelumnya)
    public function create()
    {
        return view('leave_requests.create');
    }

    // 3. STORE DATA (Simpan Izin/Sakit)
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sakit,izin,telat',
            'reason' => 'required|string|max:255',
            'file_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:3072', // Wajib foto bukti
            
            // Validasi kondisional
            'start_date' => 'required|date',
            'end_date'   => 'required_if:type,sakit,izin|nullable|date|after_or_equal:start_date',
            'start_time' => 'required_if:type,telat|nullable|date_format:H:i',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'type' => $request->type,
            'reason' => $request->reason,
            'start_date' => $request->start_date,
            'status' => 'pending', 
            'is_active' => true,
        ];

        if ($request->type === 'telat') {
            $data['start_time'] = $request->start_time;
            $data['end_date'] = null;
        } else {
            $data['end_date'] = $request->end_date;
            $data['start_time'] = null;
        }

        if ($request->hasFile('file_proof')) {
            $path = $request->file('file_proof')->store('proofs', 'public');
            $data['file_proof'] = $path;
        }

        LeaveRequest::create($data);

        return redirect()->route('leave.index')->with('success', 'Pengajuan berhasil dikirim.');
    }

    // 4. BATALKAN IZIN (User Action)
    public function cancel(LeaveRequest $leaveRequest)
    {
        // Pastikan hanya pemilik yang bisa membatalkan
        if ($leaveRequest->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Hanya bisa batal jika status masih pending atau approved (tapi user sudah datang)
        $leaveRequest->update([
            'status' => 'cancelled',
            'is_active' => false
        ]);

        return redirect()->back()->with('success', 'Izin berhasil dibatalkan/diselesaikan.');
    }

    // 5. APPROVE (Admin/Audit)
    public function approve(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Pengajuan disetujui.');
    }

    // 6. REJECT (Admin/Audit)
    public function reject(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update(['status' => 'rejected', 'is_active' => false]);
        return redirect()->back()->with('success', 'Pengajuan ditolak.');
    }
}