<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('attendance_session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('status', ['present','absent','late','half_day','leave','holiday'])->default('present');
            $table->text('remarks')->nullable(); $table->timestamps(); $table->unique(['attendance_session_id','student_id']); $table->index(['school_id','student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('attendance_records'); }
};
