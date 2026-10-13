<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('school_audit_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); $table->string('subject_type')->nullable(); $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('old_values')->nullable(); $table->json('new_values')->nullable(); $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable(); $table->timestamp('created_at');
            $table->index(['school_id','created_at']); $table->index(['school_id','subject_type','subject_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('school_audit_logs'); }
};
