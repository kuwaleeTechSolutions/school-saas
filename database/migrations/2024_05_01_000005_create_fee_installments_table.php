<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('fee_installments', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('fee_installment_plan_id')->constrained('fee_installment_plans')->cascadeOnDelete();
            $table->unsignedInteger('installment_number'); $table->string('label'); $table->decimal('percentage_of_total', 5, 2);
            $table->date('due_date'); $table->timestamps(); $table->unique(['fee_installment_plan_id','installment_number'], 'fee_installment_plan_number_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('fee_installments'); }
};
