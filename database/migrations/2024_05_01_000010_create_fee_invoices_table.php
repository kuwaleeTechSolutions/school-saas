<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete(); $table->foreignId('academic_session_id')->constrained('academic_sessions');
            $table->foreignId('fee_installment_id')->nullable()->constrained('fee_installments')->nullOnDelete();
            $table->string('invoice_number')->unique(); $table->date('due_date'); $table->decimal('subtotal_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0); $table->decimal('late_fee_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0); $table->decimal('total_amount', 12, 2); $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2);
            $table->enum('status', ['unpaid','partially_paid','paid','overdue','cancelled','refunded'])->default('unpaid');
            $table->timestamps(); $table->softDeletes(); $table->index(['school_id','student_id']); $table->index(['school_id','status']); $table->index(['school_id','due_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('fee_invoices'); }
};
