<?php

namespace App\Http\Controllers;

use App\Models\StaffTask;
use Illuminate\Http\Request;

class StaffTaskController extends Controller
{
    public function index()
    {
        return StaffTask::with('assigner')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'staff_name' => 'required|string|max:255',
            'task' => 'required|string',
            'status' => 'in:pending,in_progress,completed',
            'started' => 'nullable',
            'completed' => 'nullable',
            'quality' => 'nullable|in:excellent,good,average,poor',
            'notes' => 'nullable|string',
        ]);

        $validated['assigned_by'] = auth()->id();

        return StaffTask::create($validated);
    }

    public function show(StaffTask $staffTask)
    {
        return $staffTask->load('assigner');
    }

    public function update(Request $request, StaffTask $staffTask)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'staff_name' => 'required|string|max:255',
            'task' => 'required|string',
            'status' => 'in:pending,in_progress,completed',
            'started' => 'nullable',
            'completed' => 'nullable',
            'quality' => 'nullable|in:excellent,good,average,poor',
            'notes' => 'nullable|string',
        ]);

        $staffTask->update($validated);
        return $staffTask;
    }

    public function destroy(StaffTask $staffTask)
    {
        $staffTask->delete();
        return response()->noContent();
    }
}
