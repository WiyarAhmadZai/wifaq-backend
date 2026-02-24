<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index()
    {
        return LeaveRequest::with('user')->latest()->get();
    }

    public function store(Request $request)
    {
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

        return LeaveRequest::create($validated);
    }

    public function show(LeaveRequest $leaveRequest)
    {
        return $leaveRequest->load('user');
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
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
        return $leaveRequest;
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();
        return response()->noContent();
    }
}
