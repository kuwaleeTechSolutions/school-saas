<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); $table->string('employee_code')->nullable();
            $table->string('qualification')->nullable(); $table->string('specialization')->nullable(); $table->date('joining_date')->nullable();
            $table->enum('employment_type', ['full_time','part_time','contract'])->default('full_time');
            $table->enum('status', ['active','on_leave','inactive'])->default('active'); $table->timestamps(); $table->softDeletes();
            $table->unique(['school_id','user_id']); $table->unique(['school_id','employee_code']);
        });
    }
    public function down(): void { Schema::dropIfExists('teachers'); }
};
