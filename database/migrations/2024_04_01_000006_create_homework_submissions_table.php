<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('homework_id')->constrained('homework')->cascadeOnDelete(); $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('attachment_path')->nullable(); $table->text('student_note')->nullable(); $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['pending','submitted','late','graded'])->default('pending'); $table->decimal('grade_points', 5, 2)->nullable();
            $table->text('teacher_comment')->nullable(); $table->timestamps(); $table->unique(['homework_id','student_id']); $table->index(['school_id','student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('homework_submissions'); }
};
