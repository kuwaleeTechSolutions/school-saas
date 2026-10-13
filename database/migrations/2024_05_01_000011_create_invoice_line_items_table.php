<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('invoice_line_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained('fee_invoices')->cascadeOnDelete();
            $table->foreignId('fee_component_id')->nullable()->constrained('fee_components')->nullOnDelete(); $table->string('description');
            $table->enum('line_type', ['component','discount','late_fee','tax'])->default('component'); $table->decimal('amount', 12, 2);
            $table->timestamps(); $table->index('fee_invoice_id');
        });
    }
    public function down(): void { Schema::dropIfExists('invoice_line_items'); }
};
