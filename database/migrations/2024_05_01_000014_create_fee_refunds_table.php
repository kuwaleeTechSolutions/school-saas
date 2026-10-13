<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('fee_refunds', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('fee_payment_id')->constrained('fee_payments')->cascadeOnDelete(); $table->decimal('amount', 12, 2); $table->text('reason');
            $table->enum('status', ['requested','approved','processed','rejected'])->default('requested'); $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable(); $table->text('rejection_reason')->nullable(); $table->timestamp('processed_at')->nullable();
            $table->timestamps(); $table->index(['school_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('fee_refunds'); }
};
