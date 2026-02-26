<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id')->nullable()->unique();
            $table->string('nationality')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->enum('role', ['super_admin', 'hr_manager', 'supervisor', 'observer', 'staff'])->default('staff');
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->date('hire_date');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'probation'])->default('full_time');
            $table->enum('status', ['active', 'inactive', 'on_leave', 'suspended', 'terminated'])->default('active');
            $table->decimal('base_salary', 12, 2)->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_name')->nullable();
            $table->text('qualifications')->nullable();
            $table->text('skills')->nullable();
            $table->string('profile_photo')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
