<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use Illuminate\Http\Request;

class VisitorLogController extends Controller
{
    public function index()
    {
        return VisitorLog::with('creator')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'time_in' => 'required',
            'time_out' => 'nullable',
            'met_with' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        return VisitorLog::create($validated);
    }

    public function show(VisitorLog $visitorLog)
    {
        return $visitorLog->load('creator');
    }

    public function update(Request $request, VisitorLog $visitorLog)
    {
        $validated = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'time_in' => 'required',
            'time_out' => 'nullable',
            'met_with' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $visitorLog->update($validated);
        return $visitorLog;
    }

    public function destroy(VisitorLog $visitorLog)
    {
        $visitorLog->delete();
        return response()->noContent();
    }
}
