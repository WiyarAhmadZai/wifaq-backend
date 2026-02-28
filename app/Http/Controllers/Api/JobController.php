<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jobs = Job::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'employment_type' => 'required|in:full_time,part_time,contract,internship',
            'seats' => 'required|integer|min:1',
            'salary_range' => 'nullable|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'benefits' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'required|in:draft,open,closed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $job = Job::create([
            'position' => $request->position,
            'department' => $request->department,
            'location' => $request->location,
            'employment_type' => $request->employment_type,
            'seats' => $request->seats,
            'salary_range' => $request->salary_range,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'responsibilities' => $request->responsibilities,
            'benefits' => $request->benefits,
            'deadline' => $request->deadline,
            'status' => $request->status,
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job created successfully',
            'data' => $job
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Job $job)
    {
        return response()->json([
            'success' => true,
            'data' => $job
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Job $job)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'sometimes|required|string|max:255',
            'department' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'employment_type' => 'sometimes|required|in:full_time,part_time,contract,internship',
            'seats' => 'sometimes|required|integer|min:1',
            'salary_range' => 'nullable|string|max:255',
            'description' => 'sometimes|required|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'benefits' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'sometimes|required|in:draft,open,closed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $job->update($request->only([
            'position', 'department', 'location', 'employment_type', 'seats',
            'salary_range', 'description', 'requirements', 'responsibilities',
            'benefits', 'deadline', 'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'data' => $job
        ]);
    }

    /**
     * Update job status
     */
    public function updateStatus(Request $request, Job $job)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,open,closed',
            'status_message' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $job->update([
            'status' => $request->status,
            'status_message' => $request->status_message
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job status updated successfully',
            'data' => $job
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job)
    {
        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully'
        ]);
    }
}