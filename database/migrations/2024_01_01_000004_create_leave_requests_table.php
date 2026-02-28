<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->enum('leave_type', ['sick', 'casual', 'annual', 'emergency', 'other']);
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->integer('total_days');
            $table->text('reason');
            $table->text('coverage_plan');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};