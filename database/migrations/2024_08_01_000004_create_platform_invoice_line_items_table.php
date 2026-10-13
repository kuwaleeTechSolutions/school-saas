<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('platform_invoice_line_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('platform_invoice_id')->constrained('platform_invoices')->cascadeOnDelete();
            $table->string('description'); $table->enum('line_type',['plan_base','discount','tax'])->default('plan_base'); $table->decimal('amount',12,2); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('platform_invoice_line_items'); }
};
