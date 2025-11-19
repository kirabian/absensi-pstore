<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use App\Models\Branch;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkScheduleController extends Controller
{
    public function index()
    {
        $schedules = WorkSchedule::with(['branch', 'division'])
            ->orderBy('is_default', 'desc')
            ->orderBy('schedule_name')
            ->get();

        return view('work-schedules.index', compact('schedules'));
    }

    public function create()
    {
        $branches = Branch::all();
        $divisions = Division::all();
        
        return view('work-schedules.create', compact('branches', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'schedule_name' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'division_id' => 'nullable|exists:divisions,id',
            'check_in_start' => 'required|date_format:H:i',
            'check_in_end' => 'required|date_format:H:i|after:check_in_start',
            'check_out_start' => 'required|date_format:H:i',
            'check_out_end' => 'required|date_format:H:i|after:check_out_start',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            // Jika di set sebagai default, nonaktifkan default lainnya
            if ($request->is_default) {
                WorkSchedule::where('is_default', true)->update(['is_default' => false]);
            }

            WorkSchedule::create($request->all());
        });

        return redirect()->route('work-schedules.index')
            ->with('success', 'Jam kerja berhasil ditambahkan.');
    }

    public function edit(WorkSchedule $workSchedule)
    {
        $branches = Branch::all();
        $divisions = Division::all();
        
        return view('work-schedules.edit', compact('workSchedule', 'branches', 'divisions'));
    }

    public function update(Request $request, WorkSchedule $workSchedule)
    {
        $request->validate([
            'schedule_name' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'division_id' => 'nullable|exists:divisions,id',
            'check_in_start' => 'required|date_format:H:i',
            'check_in_end' => 'required|date_format:H:i|after:check_in_start',
            'check_out_start' => 'required|date_format:H:i',
            'check_out_end' => 'required|date_format:H:i|after:check_out_start',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $workSchedule) {
            // Jika di set sebagai default, nonaktifkan default lainnya
            if ($request->is_default) {
                WorkSchedule::where('is_default', true)
                    ->where('id', '!=', $workSchedule->id)
                    ->update(['is_default' => false]);
            }

            $workSchedule->update($request->all());
        });

        return redirect()->route('work-schedules.index')
            ->with('success', 'Jam kerja berhasil diperbarui.');
    }

    public function destroy(WorkSchedule $workSchedule)
    {
        // Cegah penghapusan schedule default
        if ($workSchedule->is_default) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus jam kerja default.');
        }

        $workSchedule->delete();

        return redirect()->route('work-schedules.index')
            ->with('success', 'Jam kerja berhasil dihapus.');
    }

    public function toggleStatus(WorkSchedule $workSchedule)
    {
        $workSchedule->update(['is_active' => !$workSchedule->is_active]);

        $status = $workSchedule->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()
            ->with('success', "Jam kerja berhasil $status.");
    }
}