<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('school_onboarding_progress', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->string('step_code');
            $table->boolean('is_completed')->default(false); $table->boolean('is_skipped')->default(false);
            $table->timestamp('completed_at')->nullable(); $table->timestamps(); $table->unique(['school_id','step_code']);
        });
    }
    public function down(): void { Schema::dropIfExists('school_onboarding_progress'); }
};
