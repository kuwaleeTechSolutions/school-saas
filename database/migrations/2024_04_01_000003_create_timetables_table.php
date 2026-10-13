<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions'); $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('section_id')->constrained('sections'); $table->enum('status', ['draft','published'])->default('draft');
            $table->unsignedBigInteger('published_by')->nullable(); $table->timestamp('published_at')->nullable();
            $table->timestamps(); $table->unique(['section_id','academic_session_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('timetables'); }
};
