<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes'); $table->foreignId('section_id')->constrained('sections');
            $table->string('roll_number')->nullable(); $table->foreignId('house_id')->nullable()->constrained('houses')->nullOnDelete();
            $table->enum('status', ['active','transferred','withdrawn','completed'])->default('active'); $table->timestamps();
            $table->unique(['student_id','academic_session_id']); $table->index(['school_id','academic_session_id','class_id','section_id'], 'student_enrol_lookup_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('student_enrollments'); }
};
