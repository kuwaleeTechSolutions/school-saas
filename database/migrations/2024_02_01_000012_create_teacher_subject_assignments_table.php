<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('teacher_subject_assignments', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete(); $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete(); $table->foreignId('section_id')->nullable()->constrained('sections')->cascadeOnDelete();
            $table->timestamps(); $table->unique(['teacher_id','subject_id','class_id','section_id'], 'tsa_unique'); $table->index('school_id');
        });
    }
    public function down(): void { Schema::dropIfExists('teacher_subject_assignments'); }
};
