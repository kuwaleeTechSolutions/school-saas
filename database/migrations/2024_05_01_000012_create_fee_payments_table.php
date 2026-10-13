<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained('fee_invoices')->cascadeOnDelete(); $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('transaction_reference')->nullable(); $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash','bank_transfer','upi','cheque','gateway'])->default('cash');
            $table->enum('status', ['pending','success','failed'])->default('success'); $table->string('gateway')->nullable();
            $table->json('gateway_response')->nullable(); $table->unsignedBigInteger('recorded_by'); $table->timestamp('paid_at');
            $table->timestamps(); $table->index(['school_id','fee_invoice_id']); $table->index(['school_id','student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('fee_payments'); }
};
