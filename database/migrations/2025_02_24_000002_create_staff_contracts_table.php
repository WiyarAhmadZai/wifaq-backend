<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('contract_number')->unique();
            $table->enum('contract_type', ['permanent', 'fixed_term', 'probation', 'consultancy', 'internship']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('probation_period_days')->default(90);
            $table->date('probation_end_date')->nullable();
            $table->enum('probation_status', ['pending', 'passed', 'failed', 'extended'])->nullable();
            $table->decimal('salary', 12, 2);
            $table->json('allowances')->nullable();
            $table->json('benefits')->nullable();
            $table->text('job_description')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('contract_file')->nullable();
            $table->enum('status', ['draft', 'active', 'expired', 'terminated', 'renewed'])->default('draft');
            $table->boolean('renewal_alert_sent')->default(false);
            $table->date('renewal_alert_date')->nullable();
            $table->foreignId('created_by')->constrained('staff');
            $table->foreignId('approved_by')->nullable()->constrained('staff');
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_contracts');
    }
};
