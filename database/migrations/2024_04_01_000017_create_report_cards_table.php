<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete(); $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->json('snapshot'); $table->string('overall_grade')->nullable(); $table->decimal('overall_percent', 5, 2)->nullable();
            $table->text('teacher_remarks')->nullable(); $table->text('principal_remarks')->nullable(); $table->string('pdf_path')->nullable();
            $table->enum('pdf_status', ['pending','queued','generated','failed'])->default('pending'); $table->timestamps();
            $table->unique(['exam_id','student_id']); $table->index(['school_id','student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('report_cards'); }
};
