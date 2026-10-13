<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('late_fee_rules', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->unsignedInteger('grace_days')->default(0);
            $table->enum('calculation_type', ['fixed','percentage_per_day','fixed_per_day'])->default('fixed'); $table->decimal('value', 10, 2);
            $table->decimal('max_amount', 10, 2)->nullable(); $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['school_id','is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('late_fee_rules'); }
};
