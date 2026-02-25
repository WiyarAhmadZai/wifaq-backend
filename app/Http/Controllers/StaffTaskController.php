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
            'staff_name' => 'required|string|max:255',
            'task' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['assigned_by'] = auth()->id();
        $validated['status'] = 'pending';

        return StaffTask::create($validated);
    }

    public function show(StaffTask $staffTask)
    {
        return $staffTask->load('assigner');
    }

    public function update(Request $request, StaffTask $staffTask)
    {
        $validated = $request->validate([
            'staff_name' => 'required|string|max:255',
            'task' => 'required|string',
            'status' => 'in:pending,in_progress,completed',
            'quality' => 'nullable|in:excellent,good,average,poor',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $staffTask->status;
        $newStatus = $validated['status'] ?? $oldStatus;

        if ($oldStatus !== 'in_progress' && $newStatus === 'in_progress') {
            $validated['started_at'] = now();
        }

        if ($oldStatus !== 'completed' && $newStatus === 'completed') {
            $validated['completed_at'] = now();
        }

        $staffTask->update($validated);
        return $staffTask->load('assigner');
    }

    public function destroy(StaffTask $staffTask)
    {
        $staffTask->delete();
        return response()->noContent();
    }
}
