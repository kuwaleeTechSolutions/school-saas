<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('admission_stage_histories', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $table->string('from_stage')->nullable(); $table->string('to_stage'); $table->unsignedBigInteger('changed_by');
            $table->text('notes')->nullable(); $table->timestamp('created_at'); $table->index(['school_id','admission_application_id'], 'admission_stage_school_app_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('admission_stage_histories'); }
};
