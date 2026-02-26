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
            $table->string('employee_id')->unique(); // Auto-generated as WEN-YY-XXXX
            $table->string('full_name');
            $table->string('department')->nullable();
            $table->enum('employment_type', ['WS', 'WLS', 'WLS-CT'])->default('WS'); // WS, WLS, WLS-CT
            $table->enum('status', ['active', 'inactive', 'on_leave', 'suspended', 'terminated'])->default('active');
            $table->decimal('base_salary', 12, 2)->nullable();
            $table->time('required_time')->nullable(); // For WS and WLS
            $table->boolean('track_attendance')->default(false); // For WS and WLS
            $table->integer('total_classes')->nullable(); // For WLS-CT
            $table->decimal('rate_per_class', 10, 2)->nullable(); // For WLS-CT
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); // Track who created the record
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); // Track who updated the record
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
