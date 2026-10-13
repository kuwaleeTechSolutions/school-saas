<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('employee_code')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->date('joining_date')->nullable();
            $table->decimal('monthly_gross_salary', 12, 2)->default(0);
            $table->unsignedInteger('annual_leave_quota')->default(12);
            $table->enum('status', ['active','on_leave','inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['school_id', 'user_id']);
            $table->unique(['school_id', 'employee_code']);
        });
    }
    public function down(): void { Schema::dropIfExists('staff_profiles'); }
};
