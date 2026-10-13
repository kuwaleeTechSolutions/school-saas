<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('platform_payments', function (Blueprint $table) {
            $table->id(); $table->string('ulid',26)->unique(); $table->foreignId('platform_invoice_id')->constrained('platform_invoices')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->enum('method',['bank_transfer','cheque','gateway'])->default('gateway');
            $table->string('gateway')->nullable(); $table->string('gateway_reference')->nullable(); $table->json('gateway_response')->nullable();
            $table->decimal('amount',12,2); $table->enum('status',['pending','success','failed'])->default('pending');
            $table->unsignedBigInteger('recorded_by')->nullable(); $table->timestamp('paid_at')->nullable(); $table->timestamps();
            $table->index(['school_id','status']); $table->index('platform_invoice_id');
        });
    }
    public function down(): void { Schema::dropIfExists('platform_payments'); }
};
