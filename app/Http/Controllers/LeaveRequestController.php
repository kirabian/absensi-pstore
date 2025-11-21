<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    // Menampilkan Form
    public function create()
    {
        return view('leave_requests.create');
    }

    // Menyimpan Data
    public function store(Request $request)
    {
        // 1. Validasi Dinamis
        $request->validate([
            'type' => 'required|in:sakit,izin,telat',
            'reason' => 'required|string|max:255',
            'file_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Bukti opsional/wajib tergantung aturan kantor
            
            // Validasi untuk SAKIT / IZIN (Butuh Tanggal Mulai & Selesai)
            'start_date' => 'required|date',
            'end_date'   => 'required_if:type,sakit,izin|nullable|date|after_or_equal:start_date',

            // Validasi untuk TELAT (Butuh Jam)
            'start_time' => 'required_if:type,telat|nullable|date_format:H:i',
        ], [
            'end_date.required_if' => 'Tanggal selesai wajib diisi untuk izin sakit/cuti.',
            'start_time.required_if' => 'Jam kedatangan wajib diisi untuk izin telat.',
        ]);

        // 2. Siapkan Data Umum
        $data = [
            'user_id' => Auth::id(),
            'type' => $request->type,
            'reason' => $request->reason,
            'start_date' => $request->start_date, // Telat pun butuh tanggal (hari ini)
            'status' => 'pending', // Default status
            'is_active' => true,
        ];

        // 3. Logika Pengisian Data Spesifik
        if ($request->type === 'telat') {
            // Kalau Telat: Set jam, kosongkan tanggal selesai
            $data['start_time'] = $request->start_time;
            $data['end_date']   = null; 
        } else {
            // Kalau Sakit/Izin: Set tanggal selesai, kosongkan jam
            $data['end_date']   = $request->end_date;
            $data['start_time'] = null;
        }

        // 4. Upload File Bukti (Jika ada)
        if ($request->hasFile('file_proof')) {
            $file = $request->file('file_proof');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Simpan di folder 'public/proofs'
            $path = $file->storeAs('proofs', $filename, 'public');
            $data['file_proof'] = $path;
        }

        // 5. Simpan ke Database
        LeaveRequest::create($data);

        return redirect()->route('leave_requests.index')->with('success', 'Pengajuan berhasil dikirim.');
    }
}