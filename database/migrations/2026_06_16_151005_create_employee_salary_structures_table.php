<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('salary_component_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('effective_from')->index();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['employee_id', 'salary_component_id', 'effective_from'], 'ess_employee_component_effective_unique');
            $table->index(['employee_id', 'is_active', 'effective_from'], 'ess_employee_active_effective_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_structures');
    }
};
