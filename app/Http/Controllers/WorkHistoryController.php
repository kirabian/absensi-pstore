<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WorkHistory;

class WorkHistoryController extends Controller
{
    /**
     * Menyimpan riwayat pekerjaan baru
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'company_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            WorkHistory::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'position' => $request->position,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
            ]);

            return redirect()->route('profile.edit')
                ->with('success', 'Riwayat pekerjaan berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal menambahkan riwayat pekerjaan: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus riwayat pekerjaan
     */
    public function destroy(WorkHistory $workHistory)
    {
        // Authorization: Pastikan user hanya bisa menghapus riwayat miliknya sendiri
        if ($workHistory->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $workHistory->delete();

            return redirect()->route('profile.edit')
                ->with('success', 'Riwayat pekerjaan berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal menghapus riwayat pekerjaan: ' . $e->getMessage());
        }
    }
}