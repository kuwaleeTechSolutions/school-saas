<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_academic_session_id')->constrained('academic_sessions'); $table->foreignId('from_class_id')->constrained('classes');
            $table->foreignId('from_section_id')->constrained('sections'); $table->foreignId('to_academic_session_id')->constrained('academic_sessions');
            $table->foreignId('to_class_id')->nullable()->constrained('classes'); $table->foreignId('to_section_id')->nullable()->constrained('sections');
            $table->enum('decision', ['promoted','retained','graduated','withdrawn'])->default('promoted'); $table->unsignedBigInteger('promoted_by');
            $table->string('batch_ulid', 26); $table->timestamps(); $table->index(['school_id','batch_ulid']); $table->index(['school_id','student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('student_promotions'); }
};
