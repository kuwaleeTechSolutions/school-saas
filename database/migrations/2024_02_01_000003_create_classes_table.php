<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('classes', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete(); $table->string('name');
            $table->unsignedInteger('sort_order')->default(0); $table->timestamps(); $table->softDeletes();
            $table->unique(['school_id','academic_session_id','name']); $table->index(['school_id','academic_session_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('classes'); }
};
