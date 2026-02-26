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
        $query = Staff::with(['supervisor', 'activeContract']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $staff = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json($staff);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|string|unique:staff,employee_id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string|unique:staff,national_id',
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'role' => 'required|in:super_admin,hr_manager,supervisor,observer,staff',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'hire_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract,probation',
            'status' => 'required|in:active,inactive,on_leave,suspended,terminated',
            'base_salary' => 'nullable|numeric|min:0',
            'bank_account' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'qualifications' => 'nullable|string',
            'skills' => 'nullable|string',
            'supervisor_id' => 'nullable|exists:staff,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);

        $staff = Staff::create($data);

        return response()->json($staff, 201);
    }

    public function show($id)
    {
        $staff = Staff::with(['supervisor', 'subordinates', 'contracts', 'activeContract'])->find($id);

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
            'employee_id' => 'sometimes|string|unique:staff,employee_id,' . $id,
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:staff,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string|unique:staff,national_id,' . $id,
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'role' => 'sometimes|in:super_admin,hr_manager,supervisor,observer,staff',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'hire_date' => 'sometimes|date',
            'employment_type' => 'sometimes|in:full_time,part_time,contract,probation',
            'status' => 'sometimes|in:active,inactive,on_leave,suspended,terminated',
            'base_salary' => 'nullable|numeric|min:0',
            'bank_account' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'qualifications' => 'nullable|string',
            'skills' => 'nullable|string',
            'supervisor_id' => 'nullable|exists:staff,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $staff->update($data);

        return response()->json($staff);
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
