<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('position_applied');
            $table->text('qualification');
            $table->text('experience');
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->string('cv_path')->nullable();
            $table->enum('status', ['new', 'reviewing', 'interview', 'hired', 'rejected'])->default('new');
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
