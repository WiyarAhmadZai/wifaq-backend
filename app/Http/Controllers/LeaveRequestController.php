<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaveRequestController extends Controller
{
    public function index()
    {
        try {
            Log::info('Fetching leave requests', ['user_id' => auth()->id()]);
            $data = LeaveRequest::with('user')->latest()->get();
            Log::info('Leave requests fetched successfully', ['count' => $data->count()]);
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error fetching leave requests', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to fetch leave requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Creating leave request', $request->all());
            
            $validated = $request->validate([
                'school' => 'required|string|max:255',
                'leave_type' => 'required|in:sick,casual,annual,emergency,other',
                'from_date' => 'required|date',
                'to_date' => 'required|date|after_or_equal:from_date',
                'total_days' => 'required|integer|min:1',
                'reason' => 'required|string',
                'coverage_plan' => 'required|string',
            ]);

            $validated['user_id'] = auth()->id();
            $validated['status'] = 'pending';

            $leaveRequest = LeaveRequest::create($validated);
            Log::info('Leave request created', ['id' => $leaveRequest->id]);
            
            return response()->json($leaveRequest, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating leave request', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to create leave request', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(LeaveRequest $leaveRequest)
    {
        try {
            Log::info('Showing leave request', ['id' => $leaveRequest->id]);
            return response()->json($leaveRequest->load('user'));
        } catch (\Exception $e) {
            Log::error('Error showing leave request', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch leave request', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        try {
            Log::info('Updating leave request', ['id' => $leaveRequest->id, 'data' => $request->all()]);
            
            $validated = $request->validate([
                'school' => 'required|string|max:255',
                'leave_type' => 'required|in:sick,casual,annual,emergency,other',
                'from_date' => 'required|date',
                'to_date' => 'required|date|after_or_equal:from_date',
                'total_days' => 'required|integer|min:1',
                'reason' => 'required|string',
                'coverage_plan' => 'required|string',
                'status' => 'in:pending,approved,rejected',
            ]);

            $leaveRequest->update($validated);
            Log::info('Leave request updated', ['id' => $leaveRequest->id]);
            
            return response()->json($leaveRequest);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating leave request', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update leave request', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        try {
            Log::info('Deleting leave request', ['id' => $leaveRequest->id]);
            $leaveRequest->delete();
            Log::info('Leave request deleted', ['id' => $leaveRequest->id]);
            return response()->noContent();
        } catch (\Exception $e) {
            Log::error('Error deleting leave request', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete leave request', 'error' => $e->getMessage()], 500);
        }
    }
}
