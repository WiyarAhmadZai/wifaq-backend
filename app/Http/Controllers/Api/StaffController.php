<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with(['supervisor', 'activeContract', 'creator', 'updater']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        if ($request->has('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $staff = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json($staff);
    }

    public function store(Request $request)
    {
        // Generate employee ID automatically
        $year = date('y'); // Last two digits of year
        $lastStaff = Staff::orderBy('id', 'desc')->first();
        if ($lastStaff) {
            $lastSequence = intval(substr($lastStaff->employee_id, -4));
            $sequenceNumber = $lastSequence + 1;
        } else {
            $sequenceNumber = 1;
        }
        $employeeId = sprintf('WEN-%s-%04d', $year, $sequenceNumber);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:100',
            'employment_type' => 'required|in:WS,WLS,WLS-CT',
            'base_salary' => 'nullable|numeric|min:0',
            'required_time' => 'nullable|date_format:H:i',
            'track_attendance' => 'nullable|boolean',
            'total_classes' => 'nullable|integer|min:0',
            'rate_per_class' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['employee_id'] = $employeeId;
        $data['status'] = 'active'; // Set default status to active
        $data['created_by'] = auth()->id(); // Automatically detect who saved the details

        $staff = Staff::create($data);

        return response()->json($staff->load(['creator', 'updater']), 201);
    }

    public function show($id)
    {
        $staff = Staff::with(['supervisor', 'subordinates', 'contracts', 'activeContract', 'creator', 'updater'])->find($id);

        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        return response()->json($staff);
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|string|max:255',
            'department' => 'nullable|string|max:100',
            'employment_type' => 'sometimes|in:WS,WLS,WLS-CT',
            'base_salary' => 'nullable|numeric|min:0',
            'required_time' => 'nullable|date_format:H:i',
            'track_attendance' => 'nullable|boolean',
            'total_classes' => 'nullable|integer|min:0',
            'rate_per_class' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['updated_by'] = auth()->id(); // Track who updated the record

        $staff->update($data);

        return response()->json($staff->load(['creator', 'updater']));
    }

    public function updateStatus(Request $request, $id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,on_leave,suspended,terminated',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $staff->update([
            'status' => $request->status,
            'updated_by' => auth()->id()
        ]);

        return response()->json($staff->load(['creator', 'updater']));
    }

    public function destroy($id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        $staff->delete();

        return response()->json(['message' => 'Staff deleted successfully']);
    }

    public function departments()
    {
        $departments = Staff::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return response()->json($departments);
    }

    public function roles()
    {
        $roles = [
            ['value' => 'super_admin', 'label' => 'Super Admin'],
            ['value' => 'hr_manager', 'label' => 'HR Manager'],
            ['value' => 'supervisor', 'label' => 'Supervisor'],
            ['value' => 'observer', 'label' => 'Observer'],
            ['value' => 'staff', 'label' => 'Staff'],
        ];

        return response()->json($roles);
    }
}
