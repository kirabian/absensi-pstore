<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Broadcast;
use App\Models\Division;
use App\Models\Branch;

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

        // Search Users - sesuai dengan relasi di model User
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('role', 'like', "%{$query}%")
            ->with(['division', 'branch'])
            ->limit(5)
            ->get()
            ->map(function ($user) {
                $divisionName = $user->division ? $user->division->name : 'No Division';
                $branchName = $user->branch ? $user->branch->name : 'No Branch';
                
                return [
                    'type' => 'user',
                    'title' => $user->name,
                    'description' => $user->email . ' - ' . $divisionName . ' (' . $branchName . ')',
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

        // Search Divisions - sesuai dengan relasi di model Division
        $divisions = Division::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with('branch')
            ->limit(5)
            ->get()
            ->map(function ($division) {
                $branchName = $division->branch ? $division->branch->name : 'No Branch';
                
                return [
                    'type' => 'division',
                    'title' => $division->name,
                    'description' => $branchName . ' - ' . ($division->description ?? 'No description'),
                    'url' => route('divisions.edit', $division->id),
                    'icon' => 'mdi-sitemap'
                ];
            });

        // Search Branches - sesuai dengan model Branch Anda
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