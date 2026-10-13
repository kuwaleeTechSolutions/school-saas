<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions'); $table->string('name');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete(); $table->string('student_category')->nullable();
            $table->boolean('requires_transport')->default(false); $table->boolean('requires_hostel')->default(false);
            $table->boolean('is_active')->default(true); $table->timestamps(); $table->softDeletes();
            $table->index(['school_id','academic_session_id','is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('fee_structures'); }
};
