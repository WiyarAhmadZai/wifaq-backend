<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        return Attendance::with(['employee', 'recorder'])->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'employee_id' => 'required|exists:users,id',
            'status' => 'required|in:present,absent,late,half_day,leave',
            'arrived' => 'nullable',
            'check_out' => 'nullable',
            'left_without_notice' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['recorded_by'] = auth()->id();

        return Attendance::create($validated);
    }

    public function show(Attendance $attendance)
    {
        return $attendance->load(['employee', 'recorder']);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'employee_id' => 'required|exists:users,id',
            'status' => 'required|in:present,absent,late,half_day,leave',
            'arrived' => 'nullable',
            'check_out' => 'nullable',
            'left_without_notice' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $attendance->update($validated);
        return $attendance;
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return response()->noContent();
    }

    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
        ]);

        $today = now()->toDateString();
        
        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $today],
            [
                'status' => 'present',
                'arrived' => now()->format('H:i'),
                'recorded_by' => auth()->id(),
            ]
        );

        return $attendance;
    }

    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
        ]);

        $today = now()->toDateString();
        
        $attendance = Attendance::where('employee_id', $validated['employee_id'])
            ->where('date', $today)
            ->first();

        if ($attendance) {
            $attendance->update(['check_out' => now()->format('H:i')]);
        }

        return $attendance;
    }
}
