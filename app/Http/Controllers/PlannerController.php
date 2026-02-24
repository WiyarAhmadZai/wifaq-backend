<?php

namespace App\Http\Controllers;

use App\Models\Planner;
use Illuminate\Http\Request;

class PlannerController extends Controller
{
    public function index()
    {
        return Planner::with('creator')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:task,meeting,event',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'day' => 'required|string|max:255',
            'time' => 'required',
            'description' => 'required|string',
            'event_type' => 'nullable|string|max:255',
            'target_audience' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'branch' => 'required|string|max:255',
            'attendance' => 'in:mandatory,optional',
            'notify_emails' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        return Planner::create($validated);
    }

    public function show(Planner $planner)
    {
        return $planner->load('creator');
    }

    public function update(Request $request, Planner $planner)
    {
        $validated = $request->validate([
            'type' => 'required|in:task,meeting,event',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'day' => 'required|string|max:255',
            'time' => 'required',
            'description' => 'required|string',
            'event_type' => 'nullable|string|max:255',
            'target_audience' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'branch' => 'required|string|max:255',
            'attendance' => 'in:mandatory,optional',
            'notify_emails' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $planner->update($validated);
        return $planner;
    }

    public function destroy(Planner $planner)
    {
        $planner->delete();
        return response()->noContent();
    }
}
