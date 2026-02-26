<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StaffContractController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\PlannerController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\StaffTaskController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VisitorLogController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('hr')->name('hr.')->group(function () {
       
        Route::prefix('staff')->name('staff.')->group(function () {
            Route::get('/list', [StaffController::class, 'index'])->name('list');
            Route::post('/store', [StaffController::class, 'store'])->name('store');
            Route::get('/show/{id}', [StaffController::class, 'show'])->name('show');
            Route::put('/update/{id}', [StaffController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [StaffController::class, 'destroy'])->name('delete');
            Route::get('/departments/list', [StaffController::class, 'departments'])->name('departments');
            Route::get('/roles/list', [StaffController::class, 'roles'])->name('roles');
        });

        Route::prefix('contracts')->name('contracts.')->group(function () {
            Route::get('/list', [StaffContractController::class, 'index'])->name('list');
            Route::post('/store', [StaffContractController::class, 'store'])->name('store');
            Route::get('/show/{id}', [StaffContractController::class, 'show'])->name('show');
            Route::put('/update/{id}', [StaffContractController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [StaffContractController::class, 'destroy'])->name('delete');
            Route::post('/approve/{id}', [StaffContractController::class, 'approve'])->name('approve');
            Route::get('/expiring-soon/list', [StaffContractController::class, 'expiringSoon'])->name('expiring-soon');
            Route::get('/types/list', [StaffContractController::class, 'contractTypes'])->name('types');
        });

        // Attendance routes
        Route::prefix('attendances')->name('attendances.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::post('/', [AttendanceController::class, 'store'])->name('store');
            Route::get('/{attendance}', [AttendanceController::class, 'show'])->name('show');
            Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
            Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
            Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
            Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
        });

        // Leave request routes
        Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
            Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
            Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('show');
            Route::put('/{leaveRequest}', [LeaveRequestController::class, 'update'])->name('update');
            Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('destroy');
        });

        // Job application routes
        Route::prefix('job-applications')->name('job-applications.')->group(function () {
            Route::get('/', [JobApplicationController::class, 'index'])->name('index');
            Route::post('/', [JobApplicationController::class, 'store'])->name('store');
            Route::get('/{jobApplication}', [JobApplicationController::class, 'show'])->name('show');
            Route::put('/{jobApplication}', [JobApplicationController::class, 'update'])->name('update');
            Route::delete('/{jobApplication}', [JobApplicationController::class, 'destroy'])->name('destroy');
        });

        // Vendor routes
        Route::prefix('vendors')->name('vendors.')->group(function () {
            Route::get('/', [VendorController::class, 'index'])->name('index');
            Route::post('/', [VendorController::class, 'store'])->name('store');
            Route::get('/{vendor}', [VendorController::class, 'show'])->name('show');
            Route::put('/{vendor}', [VendorController::class, 'update'])->name('update');
            Route::delete('/{vendor}', [VendorController::class, 'destroy'])->name('destroy');
        });

        // Purchase request routes
        Route::prefix('purchase-requests')->name('purchase-requests.')->group(function () {
            Route::get('/', [PurchaseRequestController::class, 'index'])->name('index');
            Route::post('/', [PurchaseRequestController::class, 'store'])->name('store');
            Route::get('/{purchaseRequest}', [PurchaseRequestController::class, 'show'])->name('show');
            Route::put('/{purchaseRequest}', [PurchaseRequestController::class, 'update'])->name('update');
            Route::delete('/{purchaseRequest}', [PurchaseRequestController::class, 'destroy'])->name('destroy');
        });

        // Staff task routes
        Route::prefix('staff-tasks')->name('staff-tasks.')->group(function () {
            Route::get('/', [StaffTaskController::class, 'index'])->name('index');
            Route::post('/', [StaffTaskController::class, 'store'])->name('store');
            Route::get('/{staffTask}', [StaffTaskController::class, 'show'])->name('show');
            Route::put('/{staffTask}', [StaffTaskController::class, 'update'])->name('update');
            Route::delete('/{staffTask}', [StaffTaskController::class, 'destroy'])->name('destroy');
        });

        // Planner routes
        Route::prefix('planners')->name('planners.')->group(function () {
            Route::get('/', [PlannerController::class, 'index'])->name('index');
            Route::post('/', [PlannerController::class, 'store'])->name('store');
            Route::get('/{planner}', [PlannerController::class, 'show'])->name('show');
            Route::put('/{planner}', [PlannerController::class, 'update'])->name('update');
            Route::delete('/{planner}', [PlannerController::class, 'destroy'])->name('destroy');
        });

        // Visitor log routes
        Route::prefix('visitor-logs')->name('visitor-logs.')->group(function () {
            Route::get('/', [VisitorLogController::class, 'index'])->name('index');
            Route::post('/', [VisitorLogController::class, 'store'])->name('store');
            Route::get('/{visitorLog}', [VisitorLogController::class, 'show'])->name('show');
            Route::put('/{visitorLog}', [VisitorLogController::class, 'update'])->name('update');
            Route::delete('/{visitorLog}', [VisitorLogController::class, 'destroy'])->name('destroy');
        });
    });
});

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});
