<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions'); $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('section_id')->constrained('sections'); $table->date('attendance_date');
            $table->unsignedTinyInteger('period_number')->default(0); $table->unsignedBigInteger('taken_by');
            $table->timestamp('locked_at')->nullable(); $table->timestamps();
            $table->unique(['class_id','section_id','attendance_date','period_number'], 'attendance_session_unique'); $table->index(['school_id','attendance_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('attendance_sessions'); }
};
