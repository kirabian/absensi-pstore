<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\LeaveRequest; // <-- DITAMBAHKAN
use App\Models\LateNotification; // <-- DIPERBARUI dari LateStatus
use Carbon\Carbon; // <-- DITAMBAHKAN (untuk validasi minggu)

class LeaveRequestController extends Controller
{
    /**
     * Menampilkan form untuk membuat pengajuan baru.
     */
    public function create()
    {
        // Langsung tampilkan view yang baru kita buat
        return view('leave.create');
    }

    /**
     * Menyimpan pengajuan baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $rules = [
            'type' => 'required|in:sakit,telat,cuti,libur_mingguan',
            'start_date' => 'required|date|after_or_equal:today', // Tanggal tidak boleh di masa lalu
            'reason' => 'required|string|min:10',
            'file_proof' => 'nullable|image|max:2048', // 2MB Max
        ];

        // Validasi kondisional
        if ($request->type == 'sakit' || $request->type == 'telat') {
            $rules['file_proof'] = 'required|image|max:2048';
        }

        if ($request->type == 'cuti' || $request->type == 'sakit') {
            // Untuk cuti/sakit, tanggal selesai wajib diisi
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        } else {
            // Untuk telat/libur, tanggal selesai tidak diisi di form
            $rules['end_date'] = 'nullable|date';
        }

        $validated = $request->validate($rules, [
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
            'file_proof.required' => 'Bukti foto wajib diisi untuk izin Sakit atau Telat.',
            'end_date.required' => 'Tanggal selesai wajib diisi untuk Cuti atau Sakit.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.'
        ]);

        $userId = Auth::id();

        // 2. Logika untuk Libur Mingguan (Validasi 1x per minggu)
        if ($validated['type'] == 'libur_mingguan') {
            $startDate = Carbon::parse($validated['start_date']);
            $weekStartDate = $startDate->startOfWeek(Carbon::SUNDAY)->format('Y-m-d'); // Mulai minggu hari Minggu
            $weekEndDate = $startDate->endOfWeek(Carbon::SATURDAY)->format('Y-m-d'); // Akhir minggu hari Sabtu

            $existingRequest = LeaveRequest::where('user_id', $userId)
                ->where('type', 'libur_mingguan')
                ->where('status', '!=', 'rejected') // Cek yang pending atau approved
                ->whereBetween('start_date', [$weekStartDate, $weekEndDate])
                ->first();

            if ($existingRequest) {
                return back()->with('error', 'Anda sudah mengajukan libur mingguan untuk minggu ini.')->withInput();
            }

            // Set end_date = start_date untuk libur mingguan
            $validated['end_date'] = $validated['start_date'];
        
        } else if ($validated['type'] == 'telat') {
            // Set end_date = start_date untuk Izin Telat
            $validated['end_date'] = $validated['start_date'];

            // Jika izin telat, buat juga "LateNotification" agar terdeteksi di dashboard
            // Hapus dulu (atau nonaktifkan) jika ada yang lama
            // Kita update saja yang lama menjadi tidak aktif, atau hapus
            LateNotification::where('user_id', $userId)->where('is_active', true)->delete();
            
            // Buat yang baru
            LateNotification::create([
                'user_id' => $userId,
                'message' => $validated['reason'],
                'is_active' => true, // Menggunakan field dari model Anda
            ]);
        }

        // 3. Handle Upload File (jika ada)
        $filePath = null;
        if ($request->hasFile('file_proof')) {
            // Hapus 'public/' dari path agar bisa diakses via storage:link
            $filePath = $request->file('file_proof')->store('proofs', 'public');
        }

        // 4. Simpan ke Database
        LeaveRequest::create([
            'user_id' => $userId,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? $validated['start_date'], // Default end_date jika null
            'reason' => $validated['reason'],
            'file_proof' => $filePath,
            'status' => 'pending', // Status awal
        ]);

        // 5. Redirect dengan pesan sukses
        return redirect()->route('dashboard')->with('success', 'Pengajuan berhasil dikirim dan menunggu persetujuan.');
    }
}