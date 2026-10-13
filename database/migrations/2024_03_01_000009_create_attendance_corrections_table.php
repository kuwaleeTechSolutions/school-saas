<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('attendance_record_id')->constrained('attendance_records')->cascadeOnDelete();
            $table->string('old_status'); $table->string('new_status'); $table->unsignedBigInteger('corrected_by');
            $table->text('reason')->nullable(); $table->timestamp('created_at'); $table->index(['school_id','attendance_record_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('attendance_corrections'); }
};
