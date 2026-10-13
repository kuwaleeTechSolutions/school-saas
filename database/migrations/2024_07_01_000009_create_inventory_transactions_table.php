<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->enum('type', ['receipt','issue','adjustment'])->default('receipt');
            $table->integer('quantity');
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->timestamp('transacted_at');
            $table->timestamps();
            $table->index(['school_id', 'inventory_item_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('inventory_transactions'); }
};
