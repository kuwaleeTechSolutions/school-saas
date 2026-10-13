<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('student_scholarships', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete(); $table->foreignId('scholarship_id')->constrained('scholarships')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions'); $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps(); $table->unique(['student_id','scholarship_id','academic_session_id'], 'student_scholarship_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('student_scholarships'); }
};
