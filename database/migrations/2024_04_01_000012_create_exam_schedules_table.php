<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('exam_subject_id')->constrained('exam_subjects')->cascadeOnDelete(); $table->date('exam_date'); $table->time('starts_at');
            $table->time('ends_at'); $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete(); $table->timestamps(); $table->unique('exam_subject_id');
        });
    }
    public function down(): void { Schema::dropIfExists('exam_schedules'); }
};
