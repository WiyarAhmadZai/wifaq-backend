<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['employee', 'recorder']);
        
        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }
        
        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        return $query->latest()->paginate($request->per_page ?? 15);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'employee_id' => 'required|exists:staff,id',
            'status' => 'required|in:present,absent,late,half_day,leave',
            'arrived' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after_or_equal:arrived',
            'left_without_notice' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check for duplicate attendance
        $existing = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->first();
            
        if ($existing) {
            return response()->json([
                'error' => 'Attendance record already exists for this employee on this date'
            ], 422);
        }

        $data = $request->all();
        $data['recorded_by'] = auth()->id();
        
        // Calculate working hours if both times are present
        if ($data['arrived'] && $data['check_out']) {
            $data['working_hours'] = $this->calculateWorkingHours($data['arrived'], $data['check_out']);
        }

        $attendance = Attendance::create($data);
        
        return response()->json($attendance->load(['employee', 'recorder']), 201);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['employee', 'recorder'])->find($id);
        
        if (!$attendance) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }
        
        return response()->json($attendance);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::find($id);
        
        if (!$attendance) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|date',
            'employee_id' => 'sometimes|exists:staff,id',
            'status' => 'sometimes|in:present,absent,late,half_day,leave',
            'arrived' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after_or_equal:arrived',
            'left_without_notice' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        
        // Recalculate working hours if times changed
        $arrived = $data['arrived'] ?? $attendance->arrived;
        $checkOut = $data['check_out'] ?? $attendance->check_out;
        
        if ($arrived && $checkOut) {
            $data['working_hours'] = $this->calculateWorkingHours($arrived, $checkOut);
        }

        $attendance->update($data);
        
        return response()->json($attendance->load(['employee', 'recorder']));
    }

    public function destroy($id)
    {
        $attendance = Attendance::find($id);
        
        if (!$attendance) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }
        
        $attendance->delete();
        return response()->json(['message' => 'Attendance deleted successfully']);
    }

    /**
     * Quick Check-In (One-Click)
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:staff,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $today = now()->toDateString();
        $employeeId = $request->employee_id;
        
        // Check if already checked in today
        $existing = Attendance::where('employee_id', $employeeId)
            ->where('date', $today)
            ->first();
            
        if ($existing && $existing->arrived) {
            return response()->json([
                'error' => 'Already checked in today',
                'attendance' => $existing
            ], 422);
        }

        // Create or update attendance record
        if ($existing) {
            $existing->update([
                'arrived' => now()->format('H:i'),
                'status' => 'present',
                'recorded_by' => auth()->id(),
            ]);
            $attendance = $existing;
        } else {
            $attendance = Attendance::create([
                'employee_id' => $employeeId,
                'date' => $today,
                'arrived' => now()->format('H:i'),
                'status' => 'present',
                'recorded_by' => auth()->id(),
            ]);
        }

        $attendance->refresh();

        return response()->json([
            'message' => 'Check-in successful',
            'attendance' => $attendance->load(['employee', 'recorder'])
        ]);
    }

    /**
     * Quick Check-Out (One-Click)
     */
    public function checkOut(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:staff,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $today = now()->toDateString();
            $employeeId = $request->employee_id;
            
            // Find today's attendance record
            $attendance = Attendance::where('employee_id', $employeeId)
                ->where('date', $today)
                ->first();
                
            if (!$attendance) {
                return response()->json([
                    'error' => 'No check-in record found for today. Please check in first.'
                ], 422);
            }
            
            // Check if already checked out
            if ($attendance->check_out) {
                return response()->json([
                    'error' => 'Already checked out today',
                    'attendance' => $attendance
                ], 422);
            }
            
            // Check if checked in
            if (!$attendance->arrived) {
                return response()->json([
                    'error' => 'No check-in record found. Please check in first.'
                ], 422);
            }

            $checkOutTime = now()->format('H:i');
            $workingHours = $this->calculateWorkingHours($attendance->arrived, $checkOutTime);

            $attendance->update([
                'check_out' => $checkOutTime,
                'working_hours' => $workingHours,
                'recorded_by' => auth()->id(),
            ]);

            $attendance->refresh();
            $attendance->load(['employee', 'recorder']);

            return response()->json([
                'message' => 'Check-out successful',
                'attendance' => $attendance,
                'working_hours' => $workingHours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Check-out failed: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Get today's attendance status for an employee
     */
    public function todayStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:staff,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $today = now()->toDateString();
        
        $attendance = Attendance::with(['employee', 'recorder'])
            ->where('employee_id', $request->employee_id)
            ->where('date', $today)
            ->first();

        return response()->json([
            'date' => $today,
            'has_check_in' => $attendance && $attendance->arrived ? true : false,
            'has_check_out' => $attendance && $attendance->check_out ? true : false,
            'attendance' => $attendance,
            'current_time' => now()->format('H:i')
        ]);
    }

    /**
     * Get attendance report with filters
     */
    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'employee_id' => 'nullable|exists:staff,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Attendance::with(['employee', 'recorder'])
            ->whereBetween('date', [$request->from_date, $request->to_date]);
            
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $attendances = $query->get();
        
        // Calculate summary statistics
        $summary = [
            'total_days' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
            'total_working_hours' => $attendances->sum('working_hours'),
        ];

        return response()->json([
            'attendances' => $attendances,
            'summary' => $summary
        ]);
    }

    /**
     * Calculate working hours between two times
     */
    private function calculateWorkingHours($arrived, $checkOut)
    {
        // Handle both H:i format and full datetime strings
        $arrivedTime = substr($arrived, 0, 5); // Extract HH:MM from "HH:MM:SS" or "YYYY-MM-DD HH:MM:SS"
        $checkOutTime = substr($checkOut, 0, 5);
        
        $start = \Carbon\Carbon::createFromFormat('H:i', $arrivedTime);
        $end = \Carbon\Carbon::createFromFormat('H:i', $checkOutTime);
        
        $diffInMinutes = $start->diffInMinutes($end);
        return round($diffInMinutes / 60, 2); // Return hours with 2 decimal places
    }
}
