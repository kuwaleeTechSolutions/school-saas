<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('exams', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('exam_type_id')->constrained('exam_types'); $table->foreignId('academic_session_id')->constrained('academic_sessions');
            $table->foreignId('class_id')->constrained('classes'); $table->string('name'); $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable(); $table->enum('status', ['draft','entry_open','under_review','approved','published'])->default('draft');
            $table->timestamps(); $table->softDeletes(); $table->index(['school_id','academic_session_id','class_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('exams'); }
};
