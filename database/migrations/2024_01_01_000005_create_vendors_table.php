<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('work_type');
            $table->string('contact');
            $table->text('address');
            $table->tinyInteger('quality_rating')->nullable();
            $table->tinyInteger('price_rating')->nullable();
            $table->tinyInteger('deadline_rating')->nullable();
            $table->tinyInteger('response_rating')->nullable();
            $table->text('payment_terms');
            $table->string('recommended_by');
            $table->date('date_engaged');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
