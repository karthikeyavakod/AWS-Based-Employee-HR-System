<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->dateTime('requested_check_in_at')->nullable();
            $table->dateTime('requested_check_out_at')->nullable();
            $table->string('requested_status')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('reviewer_comments')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status'], 'attendance_corrections_employee_status_index');
            $table->index(['attendance_date'], 'attendance_corrections_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
    }
};