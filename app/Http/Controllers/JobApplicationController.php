<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index()
    {
        return JobApplication::with('reviewer')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'position_applied' => 'required|string|max:255',
            'qualification' => 'required|string',
            'experience' => 'required|string',
            'expected_salary' => 'nullable|numeric',
            'cv_path' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'new';

        return JobApplication::create($validated);
    }

    public function show(JobApplication $jobApplication)
    {
        return $jobApplication->load('reviewer');
    }

    public function update(Request $request, JobApplication $jobApplication)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'position_applied' => 'required|string|max:255',
            'qualification' => 'required|string',
            'experience' => 'required|string',
            'expected_salary' => 'nullable|numeric',
            'cv_path' => 'nullable|string|max:255',
            'status' => 'in:new,reviewing,interview,hired,rejected',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['status']) && in_array($validated['status'], ['reviewing', 'interview', 'hired', 'rejected'])) {
            $validated['reviewed_by'] = auth()->id();
        }

        $jobApplication->update($validated);
        return $jobApplication;
    }

    public function destroy(JobApplication $jobApplication)
    {
        $jobApplication->delete();
        return response()->noContent();
    }
}
