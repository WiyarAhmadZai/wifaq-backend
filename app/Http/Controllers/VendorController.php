<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        return Vendor::with('creator')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'work_type' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'address' => 'required|string',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'price_rating' => 'nullable|integer|min:1|max:5',
            'deadline_rating' => 'nullable|integer|min:1|max:5',
            'response_rating' => 'nullable|integer|min:1|max:5',
            'payment_terms' => 'required|string',
            'recommended_by' => 'required|string|max:255',
            'date_engaged' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        return Vendor::create($validated);
    }

    public function show(Vendor $vendor)
    {
        return $vendor->load(['creator', 'purchaseRequests']);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:255',
            'work_type' => 'sometimes|required|string|max:255',
            'contact' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'quality_rating' => 'sometimes|nullable|integer|min:1|max:5',
            'price_rating' => 'sometimes|nullable|integer|min:1|max:5',
            'deadline_rating' => 'sometimes|nullable|integer|min:1|max:5',
            'response_rating' => 'sometimes|nullable|integer|min:1|max:5',
            'payment_terms' => 'sometimes|required|string',
            'recommended_by' => 'sometimes|required|string|max:255',
            'date_engaged' => 'sometimes|required|date',
            'notes' => 'sometimes|nullable|string',
        ]);

        $vendor->update($validated);
        return $vendor;
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return response()->noContent();
    }
}
