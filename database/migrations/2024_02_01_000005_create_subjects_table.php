<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->string('name');
            $table->string('code')->nullable(); $table->timestamps(); $table->softDeletes(); $table->unique(['school_id','name']);
        });
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id(); $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete(); $table->timestamps(); $table->unique(['class_id','subject_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('class_subjects'); Schema::dropIfExists('subjects'); }
};
