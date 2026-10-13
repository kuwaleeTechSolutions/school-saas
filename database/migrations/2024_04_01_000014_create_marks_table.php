<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('marks', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('exam_subject_id')->constrained('exam_subjects')->cascadeOnDelete(); $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('marks_obtained', 6, 2)->nullable(); $table->boolean('is_absent')->default(false);
            $table->unsignedBigInteger('entered_by')->nullable(); $table->timestamps();
            $table->unique(['exam_subject_id','student_id']); $table->index(['school_id','student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('marks'); }
};
