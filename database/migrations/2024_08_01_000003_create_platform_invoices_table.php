<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('platform_invoices', function (Blueprint $table) {
            $table->id(); $table->string('ulid',26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete(); $table->string('invoice_number')->unique();
            $table->date('period_starts_on'); $table->date('period_ends_on'); $table->date('due_date');
            $table->decimal('subtotal_amount',12,2); $table->decimal('discount_amount',12,2)->default(0); $table->decimal('tax_amount',12,2)->default(0);
            $table->decimal('total_amount',12,2); $table->decimal('paid_amount',12,2)->default(0); $table->decimal('balance_amount',12,2);
            $table->foreignId('coupon_redemption_id')->nullable()->constrained('coupon_redemptions')->nullOnDelete();
            $table->enum('status',['unpaid','partially_paid','paid','overdue','cancelled','refunded'])->default('unpaid');
            $table->timestamps(); $table->softDeletes(); $table->index(['school_id','status']); $table->index(['school_id','due_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('platform_invoices'); }
};
