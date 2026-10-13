<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('fee_structure_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete();
            $table->foreignId('fee_component_id')->constrained('fee_components'); $table->decimal('annual_amount', 12, 2);
            $table->timestamps(); $table->unique(['fee_structure_id','fee_component_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('fee_structure_items'); }
};
