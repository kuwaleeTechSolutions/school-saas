<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete(); $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('attachment_path')->nullable(); $table->text('student_note')->nullable(); $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['pending','submitted','late','graded'])->default('pending'); $table->decimal('marks_obtained', 6, 2)->nullable();
            $table->text('teacher_comment')->nullable(); $table->timestamps(); $table->unique(['assignment_id','student_id']); $table->index(['school_id','student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('assignment_submissions'); }
};
