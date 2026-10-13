<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->enum('category', ['asset','consumable'])->default('consumable');
            $table->string('unit')->default('pcs');
            $table->unsignedInteger('reorder_level')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['school_id', 'name']);
        });
    }
    public function down(): void { Schema::dropIfExists('inventory_items'); }
};
