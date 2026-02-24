<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_tasks', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('staff_name');
            $table->text('task');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->time('started')->nullable();
            $table->time('completed')->nullable();
            $table->enum('quality', ['excellent', 'good', 'average', 'poor'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_tasks');
    }
};
