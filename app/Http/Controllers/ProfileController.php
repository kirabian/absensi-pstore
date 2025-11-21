<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\WorkHistory;
use App\Models\Inventory;

class ProfileController extends Controller
{
    /**
     * Menampilkan form edit profil.
     */
    public function edit()
    {
        $user = Auth::user();
        $workHistories = $user->workHistories;
        $inventories = $user->inventories()->latest()->get();

        return view('profile.edit', compact('user', 'workHistories', 'inventories'));
    }

    /**
     * Update data TEKS (Nama, Email, Sosmed, Password).
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'whatsapp' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:100',
            'linkedin' => 'nullable|string|max:100',
        ]);

        $data = $request->only([
            'name',
            'email',
            'whatsapp',
            'instagram',
            'tiktok',
            'facebook',
            'linkedin'
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('profile.edit')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Update HANYA foto profil.
     */
    public function updatePhoto(Request $request)
{
    $request->validate([
        'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:51200', // 2MB
    ]);

    /** @var \App\Models\User $user */
    $user = Auth::user();

    try {
        // Hapus foto lama jika ada
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Simpan foto baru
        $path = $request->file('profile_photo')->store('profile_photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        return redirect()->route('profile.edit')
            ->with('success', 'Foto profil berhasil di-upload.');

    } catch (\Exception $e) {
        return redirect()->route('profile.edit')
            ->with('error', 'Gagal mengupload foto: ' . $e->getMessage());
    }
}

    /**
     * Update HANYA KTP (sekali upload).
     */
    public function updateKtp(Request $request)
    {
        $request->validate([
            'ktp_photo' => 'required|image|mimes:jpeg,png,jpg|max:51200', // 2MB
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Hanya izinkan upload JIKA KTP masih kosong
        if (!$user->ktp_photo_path) {
            $path = $request->file('ktp_photo')->store('ktp_photos', 'public');
            $user->update(['ktp_photo_path' => $path]);
            return redirect()->route('profile.edit')
                ->with('success', 'KTP berhasil di-upload.');
        }

        return redirect()->route('profile.edit')
            ->with('error', 'KTP sudah pernah di-upload dan tidak bisa diubah.');
    }

    /**
     * Menghapus foto profil user
     */
    public function deleteProfilePhoto()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Foto profil dihapus.');
    }

    /**
     * Menyimpan inventaris baru
     */
    public function storeInventory(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|in:elektronik,perkantoran,kendaraan,lainnya',
            'serial_number' => 'nullable|string|max:100',
            'received_date' => 'required|date',
            'condition' => 'required|string|in:baik,rusak_ringan,rusak_berat,perbaikan',
            'description' => 'nullable|string|max:1000',
            'item_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB
        ]);

        try {
            $data = [
                'user_id' => $user->id,
                'item_name' => $request->item_name,
                'category' => $request->category,
                'serial_number' => $request->serial_number,
                'received_date' => $request->received_date,
                'condition' => $request->condition,
                'description' => $request->description,
            ];

            // Simpan foto barang jika ada
            if ($request->hasFile('item_photo')) {
                $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
            }

            // Simpan dokumen jika ada
            if ($request->hasFile('document')) {
                $data['document_path'] = $request->file('document')->store('inventory_documents', 'public');
            }

            Inventory::create($data);

            return redirect()->route('profile.edit')
                ->with('success', 'Inventaris berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal menambahkan inventaris: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus inventaris
     */
    public function destroyInventory(Inventory $inventory)
    {
        // Authorization: Pastikan user hanya bisa menghapus inventaris miliknya sendiri
        if ($inventory->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Hapus file foto jika ada
            if ($inventory->item_photo_path) {
                Storage::disk('public')->delete($inventory->item_photo_path);
            }

            // Hapus file dokumen jika ada
            if ($inventory->document_path) {
                Storage::disk('public')->delete($inventory->document_path);
            }

            $inventory->delete();

            return redirect()->route('profile.edit')
                ->with('success', 'Inventaris berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal menghapus inventaris: ' . $e->getMessage());
        }
    }
}
