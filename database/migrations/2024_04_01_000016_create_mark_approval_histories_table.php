<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('mark_approval_histories', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('exam_subject_id')->constrained('exam_subjects')->cascadeOnDelete(); $table->string('from_status')->nullable();
            $table->string('to_status'); $table->unsignedBigInteger('actor_id'); $table->text('notes')->nullable();
            $table->timestamp('created_at'); $table->index(['school_id','exam_subject_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('mark_approval_histories'); }
};
