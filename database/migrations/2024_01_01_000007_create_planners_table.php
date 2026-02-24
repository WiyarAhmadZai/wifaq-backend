<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planners', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['task', 'meeting', 'event']);
            $table->string('name');
            $table->date('date');
            $table->string('day');
            $table->time('time');
            $table->text('description');
            $table->string('event_type')->nullable();
            $table->string('target_audience')->nullable();
            $table->string('location')->nullable();
            $table->string('branch');
            $table->enum('attendance', ['mandatory', 'optional'])->default('optional');
            $table->text('notify_emails')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planners');
    }
};
