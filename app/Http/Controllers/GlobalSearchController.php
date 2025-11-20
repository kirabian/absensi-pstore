<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Broadcast;
use App\Models\Division;
use App\Models\Branch;
use App\Models\Attendance;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $results = [];
        
        if (!$query || strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $user = Auth::user();

        // Hanya admin yang bisa melakukan global search
        if ($user->role !== 'admin') {
            return response()->json(['results' => []]);
        }

        // Search Users
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('role', 'like', "%{$query}%")
            ->with(['division', 'branch'])
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'user',
                    'title' => $user->name,
                    'description' => $user->email . ' - ' . $user->role,
                    'url' => route('users.edit', $user->id),
                    'icon' => 'mdi-account'
                ];
            });

        // Search Broadcasts
        $broadcasts = Broadcast::where('title', 'like', "%{$query}%")
            ->orWhere('message', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($broadcast) {
                return [
                    'type' => 'broadcast',
                    'title' => $broadcast->title,
                    'description' => Str::limit($broadcast->message, 50),
                    'url' => route('broadcast.show', $broadcast->id),
                    'icon' => 'mdi-bullhorn'
                ];
            });

        // Search Divisions
        $divisions = Division::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with('branch')
            ->limit(5)
            ->get()
            ->map(function ($division) {
                return [
                    'type' => 'division',
                    'title' => $division->name,
                    'description' => $division->branch->name ?? 'No Branch',
                    'url' => route('divisions.edit', $division->id),
                    'icon' => 'mdi-sitemap'
                ];
            });

        // Search Branches
        $branches = Branch::where('name', 'like', "%{$query}%")
            ->orWhere('address', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($branch) {
                return [
                    'type' => 'branch',
                    'title' => $branch->name,
                    'description' => $branch->address,
                    'url' => route('branches.edit', $branch->id),
                    'icon' => 'mdi-office-building'
                ];
            });

        // Gabungkan semua hasil
        $results = $users->merge($broadcasts)->merge($divisions)->merge($branches)->take(10);

        return response()->json(['results' => $results]);
    }
}