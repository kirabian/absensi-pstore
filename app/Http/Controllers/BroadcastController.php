<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = Broadcast::published()
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('broadcast.index', compact('broadcasts'));
    }

    public function create()
    {
        // Hanya admin yang bisa membuat broadcast
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return view('broadcast.create');
    }

    public function store(Request $request)
    {
        // Hanya admin yang bisa membuat broadcast
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high'
        ]);

        Broadcast::create([
            'title' => $request->title,
            'message' => $request->message,
            'priority' => $request->priority,
            'created_by' => Auth::id(),
            'is_published' => true,
            'published_at' => now()
        ]);

        return redirect()->route('broadcast.index')
            ->with('success', 'Broadcast berhasil dikirim!');
    }

    public function show(Broadcast $broadcast)
    {
        if (!$broadcast->is_published) {
            abort(404);
        }

        return view('broadcast.show', compact('broadcast'));
    }

    // TAMBAHKAN METHOD EDIT
    public function edit(Broadcast $broadcast)
    {
        // Hanya admin yang bisa edit broadcast
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return view('broadcast.edit', compact('broadcast'));
    }

    // TAMBAHKAN METHOD UPDATE
    public function update(Request $request, Broadcast $broadcast)
    {
        // Hanya admin yang bisa update broadcast
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high'
        ]);

        $broadcast->update([
            'title' => $request->title,
            'message' => $request->message,
            'priority' => $request->priority
        ]);

        return redirect()->route('broadcast.index')
            ->with('success', 'Broadcast berhasil diperbarui!');
    }

    public function destroy(Broadcast $broadcast)
    {
        // Hanya admin yang bisa menghapus broadcast
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $broadcast->delete();

        return redirect()->route('broadcast.index')
            ->with('success', 'Broadcast berhasil dihapus!');
    }
}