<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class JobApplicationController extends Controller
{
    public function index()
    {
        return JobApplication::with('reviewer')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'position_applied' => 'required|string|max:255',
            'qualification' => 'required|string',
            'experience' => 'required|string',
            'expected_salary' => 'nullable|numeric',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // Max 10MB, allow PDF, DOC, DOCX
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'new';

        // Handle CV file upload if present
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('job_applications/cvs', 'public');
            $validated['cv_path'] = $cvPath;
        }

        return JobApplication::create($validated);
    }

    public function show(JobApplication $jobApplication)
    {
        return $jobApplication->load('reviewer');
    }

    public function update(Request $request, JobApplication $jobApplication)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|required|string|max:255',
            'position_applied' => 'sometimes|required|string|max:255',
            'qualification' => 'sometimes|required|string',
            'experience' => 'sometimes|required|string',
            'expected_salary' => 'sometimes|nullable|numeric',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // Max 10MB, allow PDF, DOC, DOCX
            'status' => 'sometimes|in:new,reviewing,interview,hired,rejected',
            'notes' => 'sometimes|nullable|string',
            'status_message' => 'sometimes|nullable|string', // Added for status update message
        ]);

        $previousStatus = $jobApplication->status;
        $statusChanged = isset($validated['status']) && $validated['status'] !== $previousStatus;
        $statusMessage = $request->input('status_message', ''); // Get status message from request

        if (isset($validated['status']) && in_array($validated['status'], ['reviewing', 'interview', 'hired', 'rejected'])) {
            $validated['reviewed_by'] = auth()->id();
        }

        // Handle CV file upload if present
        if ($request->hasFile('cv')) {
            // Delete old CV if exists
            if ($jobApplication->cv_path) {
                Storage::disk('public')->delete($jobApplication->cv_path);
            }
            $cvPath = $request->file('cv')->store('job_applications/cvs', 'public');
            $validated['cv_path'] = $cvPath;
        }

        $jobApplication->update($validated);
        
        // Send email notification if status changed and message provided
        if ($statusChanged && !empty($statusMessage)) {
            $this->sendStatusUpdateEmail($jobApplication, $previousStatus, $statusMessage);
        }

        return $jobApplication;
    }

    private function sendStatusUpdateEmail(JobApplication $jobApplication, $previousStatus, $message)
    {
        try {
            $subject = "Job Application Status Updated - {$jobApplication->position_applied}";
            $body = "Dear {$jobApplication->full_name},

Your job application status has been updated from '{$previousStatus}' to '{$jobApplication->status}'.

Message: {$message}

Position Applied: {$jobApplication->position_applied}
Application ID: {$jobApplication->id}

Best regards,
HR Team";
            Mail::raw($body, function ($mail) use ($jobApplication, $subject) {
                $mail->to($jobApplication->email)
                     ->from(config('mail.from.address', 'mrwiyarahmadzai@gmail.com'), 'HR Team')
                     ->subject($subject);
            });

            Log::info("Status update email sent successfully to {$jobApplication->email}", [
                'application_id' => $jobApplication->id,
                'new_status' => $jobApplication->status,
                'previous_status' => $previousStatus,
                'message_sent' => $message
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send status update email", [
                'error' => $e->getMessage(),
                'application_id' => $jobApplication->id,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            // For debugging - you might want to temporarily throw the exception to see it
            // throw $e; // Uncomment this temporarily to see the actual error
        }
    }

    public function destroy(JobApplication $jobApplication)
    {
        // Delete CV file if exists
        if ($jobApplication->cv_path) {
            Storage::disk('public')->delete($jobApplication->cv_path);
        }
        $jobApplication->delete();
        return response()->noContent();
    }
}
