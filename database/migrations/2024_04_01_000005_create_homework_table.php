<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('homework', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('section_id')->constrained('sections'); $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('teachers'); $table->string('title'); $table->text('instructions')->nullable();
            $table->string('attachment_path')->nullable(); $table->date('assigned_date'); $table->date('due_date'); $table->timestamps(); $table->softDeletes();
            $table->index(['school_id','section_id','due_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('homework'); }
};
