<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaffContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffContractController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffContract::with(['staff', 'creator', 'approver']);

        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('contract_type')) {
            $query->where('contract_type', $request->contract_type);
        }

        if ($request->has('expiring_soon')) {
            $query->expiringSoon($request->expiring_soon);
        }

        $contracts = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json($contracts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:staff,id',
            'contract_type' => 'required|in:permanent,fixed_term,probation,consultancy,internship',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'probation_period_days' => 'nullable|integer|min:0',
            'salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|array',
            'benefits' => 'nullable|array',
            'job_description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'contract_file' => 'nullable|string',
            'status' => 'required|in:draft,active,expired,terminated,renewed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate contract number automatically
        $year = date('y'); // Last two digits of year
        $lastContract = StaffContract::orderBy('id', 'desc')->first();
        if ($lastContract && preg_match('/WEN-CT-(\d{2})-(\d{4})/', $lastContract->contract_number, $matches)) {
            $lastSequence = intval($matches[2]);
            $sequenceNumber = $lastSequence + 1;
        } else {
            $sequenceNumber = 1;
        }
        $contractNumber = sprintf('WEN-CT-%s-%04d', $year, $sequenceNumber);

        $data = $request->all();
        $data['contract_number'] = $contractNumber;
        $data['created_by'] = auth()->id();

        if ($data['contract_type'] === 'probation' && isset($data['probation_period_days'])) {
            $data['probation_end_date'] = \Carbon\Carbon::parse($data['start_date'])
                ->addDays($data['probation_period_days']);
            $data['probation_status'] = 'pending';
        }

        $contract = StaffContract::create($data);

        return response()->json($contract->load(['staff', 'creator']), 201);
    }

    public function show($id)
    {
        $contract = StaffContract::with(['staff', 'creator', 'approver'])->find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        return response()->json($contract);
    }

    public function update(Request $request, $id)
    {
        $contract = StaffContract::find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'contract_type' => 'sometimes|in:permanent,fixed_term,probation,consultancy,internship',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'probation_period_days' => 'nullable|integer|min:0',
            'probation_status' => 'nullable|in:pending,passed,failed,extended',
            'salary' => 'sometimes|numeric|min:0',
            'allowances' => 'nullable|array',
            'benefits' => 'nullable|array',
            'job_description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'contract_file' => 'nullable|string',
            'status' => 'sometimes|in:draft,active,expired,terminated,renewed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if (isset($data['start_date']) && isset($data['probation_period_days'])) {
            $data['probation_end_date'] = \Carbon\Carbon::parse($data['start_date'])
                ->addDays($data['probation_period_days']);
        }

        $contract->update($data);

        return response()->json($contract->load(['staff', 'creator', 'approver']));
    }

    public function destroy($id)
    {
        $contract = StaffContract::find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        $contract->delete();

        return response()->json(['message' => 'Contract deleted successfully']);
    }

    public function updateStatus(Request $request, $id)
    {
        $contract = StaffContract::find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,active,expired,terminated,renewed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contract->update([
            'status' => $request->status,
            'updated_by' => auth()->id()
        ]);

        return response()->json($contract->load(['staff', 'creator', 'approver']));
    }

    public function approve(Request $request, $id)
    {
        $contract = StaffContract::find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contract not found'], 404);
        }

        $contract->update([
            'status' => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json($contract->load(['staff', 'creator', 'approver']));
    }

    public function expiringSoon(Request $request)
    {
        $days = $request->days ?? 30;
        $contracts = StaffContract::with('staff')
            ->expiringSoon($days)
            ->orderBy('end_date')
            ->get();

        return response()->json($contracts);
    }

    public function contractTypes()
    {
        $types = [
            ['value' => 'permanent', 'label' => 'Permanent'],
            ['value' => 'fixed_term', 'label' => 'Fixed Term'],
            ['value' => 'probation', 'label' => 'Probation'],
            ['value' => 'consultancy', 'label' => 'Consultancy'],
            ['value' => 'internship', 'label' => 'Internship'],
        ];

        return response()->json($types);
    }
}
