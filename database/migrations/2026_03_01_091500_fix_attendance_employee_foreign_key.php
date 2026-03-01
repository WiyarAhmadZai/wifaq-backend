<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the existing foreign key constraint
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        // Add the new foreign key constraint referencing staff table
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('staff');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('users');
        });
    }
};
