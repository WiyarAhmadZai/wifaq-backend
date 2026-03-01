<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Staff;
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

        $recordedBy = auth()->id();
        if ($recordedBy === null) {
            return response()->json(['error' => 'Unauthenticated. Please log in to record attendance.'], 401);
        }

        $data = array_intersect_key($request->only([
            'date', 'employee_id', 'status', 'arrived', 'check_out', 'left_without_notice', 'notes'
        ]), array_flip((new Attendance())->getFillable()));
        $data['recorded_by'] = $recordedBy;

        // Only set times when present; for absent leave null
        $arrived = isset($data['arrived']) && $data['arrived'] !== '' ? $data['arrived'] : null;
        $checkOut = isset($data['check_out']) && $data['check_out'] !== '' ? $data['check_out'] : null;
        $data['arrived'] = $arrived;
        $data['check_out'] = $checkOut;

        if ($arrived && $checkOut) {
            $data['working_hours'] = $this->calculateWorkingHours($arrived, $checkOut);
        }

        try {
            $attendance = Attendance::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            $message = 'Failed to save attendance.';
            if (str_contains($e->getMessage(), 'foreign key') || str_contains($e->getMessage(), 'recorded_by')) {
                $message = 'Invalid user or employee. Please ensure you are logged in and the employee exists.';
            }
            return response()->json(['error' => $message], 422);
        }

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
     * Get daily sheet: all staff for a given date with their attendance or "pending".
     * No record + no check-in = pending (not counted as absent). Only explicit mark = absent.
     */
    public function dailySheet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $date = $request->date;
        $staff = Staff::orderBy('full_name')->get();
        $attendances = Attendance::where('date', $date)
            ->with(['employee', 'recorder'])
            ->get()
            ->keyBy('employee_id');

        $rows = $staff->map(function ($employee, $index) use ($attendances, $date) {
            $attendance = $attendances->get($employee->id);
            $status = $attendance ? $attendance->status : 'pending';
            return [
                'index' => $index + 1,
                'employee' => [
                    'id' => $employee->id,
                    'full_name' => $employee->full_name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department,
                    'initials' => $this->getInitials($employee->full_name),
                ],
                'attendance_id' => $attendance?->id,
                'status' => $status,
                'arrived' => $attendance?->arrived ? substr($attendance->arrived, 0, 5) : null,
                'check_out' => $attendance?->check_out ? substr($attendance->check_out, 0, 5) : null,
                'working_hours' => $attendance?->working_hours,
            ];
        });

        return response()->json([
            'date' => $date,
            'rows' => $rows,
        ]);
    }

    private function getInitials($fullName)
    {
        $parts = array_filter(explode(' ', trim($fullName)));
        if (empty($parts)) {
            return '?';
        }
        if (count($parts) === 1) {
            return strtoupper(substr($parts[0], 0, 2));
        }
        return strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
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
