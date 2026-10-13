<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id(); $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->enum('billing_cycle', ['monthly','quarterly','half_yearly','annual']); $table->decimal('price', 12, 2);
            $table->decimal('setup_fee', 12, 2)->default(0); $table->decimal('discount_percent', 5, 2)->default(0);
            $table->string('currency', 3)->default('INR'); $table->boolean('tax_applicable')->default(true);
            $table->timestamps(); $table->unique(['plan_id','billing_cycle']);
        });
    }
    public function down(): void { Schema::dropIfExists('plan_prices'); }
};
