<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VisitorLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\StaffTaskController;
use App\Http\Controllers\PlannerController;
use App\Http\Controllers\JobApplicationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('hr')->group(function () {
        Route::get('/visitor-logs', [VisitorLogController::class, 'index']);
        Route::post('/visitor-logs', [VisitorLogController::class, 'store']);
        Route::get('/visitor-logs/{visitorLog}', [VisitorLogController::class, 'show']);
        Route::put('/visitor-logs/{visitorLog}', [VisitorLogController::class, 'update']);
        Route::delete('/visitor-logs/{visitorLog}', [VisitorLogController::class, 'destroy']);

        Route::get('/attendances', [AttendanceController::class, 'index']);
        Route::post('/attendances', [AttendanceController::class, 'store']);
        Route::get('/attendances/{attendance}', [AttendanceController::class, 'show']);
        Route::put('/attendances/{attendance}', [AttendanceController::class, 'update']);
        Route::delete('/attendances/{attendance}', [AttendanceController::class, 'destroy']);
        Route::post('/attendances/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/attendances/check-out', [AttendanceController::class, 'checkOut']);

        Route::get('/purchase-requests', [PurchaseRequestController::class, 'index']);
        Route::post('/purchase-requests', [PurchaseRequestController::class, 'store']);
        Route::get('/purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'show']);
        Route::put('/purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'update']);
        Route::delete('/purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'destroy']);

        Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
        Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
        Route::get('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show']);
        Route::put('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'update']);
        Route::delete('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'destroy']);

        Route::get('/vendors', [VendorController::class, 'index']);
        Route::post('/vendors', [VendorController::class, 'store']);
        Route::get('/vendors/{vendor}', [VendorController::class, 'show']);
        Route::put('/vendors/{vendor}', [VendorController::class, 'update']);
        Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy']);

        Route::get('/staff-tasks', [StaffTaskController::class, 'index']);
        Route::post('/staff-tasks', [StaffTaskController::class, 'store']);
        Route::get('/staff-tasks/{staffTask}', [StaffTaskController::class, 'show']);
        Route::put('/staff-tasks/{staffTask}', [StaffTaskController::class, 'update']);
        Route::delete('/staff-tasks/{staffTask}', [StaffTaskController::class, 'destroy']);

        Route::get('/planners', [PlannerController::class, 'index']);
        Route::post('/planners', [PlannerController::class, 'store']);
        Route::get('/planners/{planner}', [PlannerController::class, 'show']);
        Route::put('/planners/{planner}', [PlannerController::class, 'update']);
        Route::delete('/planners/{planner}', [PlannerController::class, 'destroy']);

        Route::get('/job-applications', [JobApplicationController::class, 'index']);
        Route::post('/job-applications', [JobApplicationController::class, 'store']);
        Route::get('/job-applications/{jobApplication}', [JobApplicationController::class, 'show']);
        Route::put('/job-applications/{jobApplication}', [JobApplicationController::class, 'update']);
        Route::delete('/job-applications/{jobApplication}', [JobApplicationController::class, 'destroy']);
    });
});

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});
