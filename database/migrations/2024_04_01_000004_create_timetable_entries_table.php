<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->enum('day_of_week', ['monday','tuesday','wednesday','thursday','friday','saturday']);
            $table->foreignId('timetable_period_id')->constrained('timetable_periods'); $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('teachers'); $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->timestamps(); $table->unique(['timetable_id','day_of_week','timetable_period_id'], 'timetable_entry_cell_unique');
            $table->index(['school_id','day_of_week','timetable_period_id'], 'timetable_school_day_period_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('timetable_entries'); }
};
