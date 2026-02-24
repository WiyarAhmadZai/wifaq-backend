<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        return PurchaseRequest::with('user')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'branch' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'urgency' => 'required|in:low,medium,high',
            'item' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'estimated_cost' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        return PurchaseRequest::create($validated);
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        return $purchaseRequest->load('user');
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'branch' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'urgency' => 'required|in:low,medium,high',
            'item' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'estimated_cost' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'status' => 'in:pending,approved,rejected',
        ]);

        $purchaseRequest->update($validated);
        return $purchaseRequest;
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->delete();
        return response()->noContent();
    }
}
