<?php

namespace App\Http\Controllers;

use App\Models\Branch; // <-- Panggil model Branch
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    /**
     * Filter agar hanya Super Admin yang bisa akses.
     */
    public function __construct()
    {
        // Middleware ini akan mengecek SETIAP fungsi di controller ini
        $this->middleware(function ($request, $next) {
            // Cek jika user adalah admin DAN branch_id-nya KOSONG (Super Admin)
            if (Auth::check() && Auth::user()->role == 'admin' && Auth::user()->branch_id == null) {
                return $next($request); // Lanjutkan
            }
            // Jika bukan, lempar error 403 (Akses Ditolak)
            return abort(403, 'Hanya Super Admin yang boleh mengakses halaman ini.');
        });
    }

    /**
     * Menampilkan daftar semua cabang.
     */
    public function index()
    {
        $branches = Branch::latest()->get();
        return view('branch.branch_index', compact('branches'));
    }

    /**
     * Menampilkan form untuk membuat cabang baru.
     */
    public function create()
    {
        return view('branch.branch_create');
    }

    /**
     * Menyimpan cabang baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches',
            'address' => 'nullable|string',
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Cabang baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit cabang.
     */
    public function edit(Branch $branch)
    {
        return view('branch.branch_edit', compact('branch'));
    }

    /**
     * Update data cabang di database.
     */
    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'nullable|string',
        ]);

        $branch->update($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Data cabang berhasil diperbarui.');
    }

    /**
     * Menghapus cabang dari database.
     */
    public function destroy(Branch $branch)
    {
        try {
            $branch->delete();
            return redirect()->route('branches.index')
                ->with('success', 'Cabang berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangkap error jika cabang tidak bisa dihapus (karena masih dipakai)
            return redirect()->route('branches.index')
                ->with('error', 'Gagal menghapus cabang. Pastikan tidak ada user/divisi yang terhubung ke cabang ini.');
        }
    }

    // Kita tidak pakai 'show'
    public function show(Branch $branch)
    {
        return redirect()->route('branches.edit', $branch->id);
    }
}
