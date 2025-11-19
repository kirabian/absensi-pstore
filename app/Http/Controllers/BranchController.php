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
    $this->middleware(function ($request, $next) {

        if (
            Auth::check() &&
            in_array(Auth::user()->role, ['admin', 'audit']) &&   // admin ATAU audit
            Auth::user()->branch_id == null                      // super admin (tanpa branch)
        ) {
            return $next($request);
        }

        return abort(403, 'Hanya Super Admin (Admin atau Audit) yang boleh mengakses halaman ini.');
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
