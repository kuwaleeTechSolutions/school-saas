<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('school_usage_counters', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->string('metric');
            $table->unsignedBigInteger('current_value')->default(0); $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable(); $table->timestamps();
            $table->unique(['school_id','metric','period_start']); $table->index(['school_id','metric']);
        });
    }
    public function down(): void { Schema::dropIfExists('school_usage_counters'); }
};
