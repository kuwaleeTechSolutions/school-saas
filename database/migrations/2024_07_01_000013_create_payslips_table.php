<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->decimal('gross_salary', 12, 2);
            $table->unsignedInteger('unpaid_leave_days')->default(0);
            $table->decimal('leave_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2);
            $table->string('pdf_path')->nullable();
            $table->enum('pdf_status', ['pending','queued','generated','failed'])->default('pending');
            $table->timestamps();
            $table->unique(['payroll_run_id', 'staff_profile_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('payslips'); }
};
