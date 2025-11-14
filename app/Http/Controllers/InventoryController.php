<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|in:elektronik,perkantoran,kendaraan,lainnya',
            'serial_number' => 'nullable|string|max:100',
            'received_date' => 'required|date',
            'condition' => 'required|string|in:baik,rusak_ringan,rusak_berat,perbaikan',
            'description' => 'nullable|string|max:1000',
            'item_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        try {
            $data = $request->all();
            $data['user_id'] = $user->id;

            if ($request->hasFile('item_photo')) {
                $data['item_photo_path'] = $request->file('item_photo')->store('inventory_photos', 'public');
            }

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

    public function destroy(Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if ($inventory->item_photo_path) {
                Storage::disk('public')->delete($inventory->item_photo_path);
            }

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