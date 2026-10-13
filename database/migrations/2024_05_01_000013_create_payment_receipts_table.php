<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('fee_payment_id')->constrained('fee_payments')->cascadeOnDelete(); $table->string('receipt_number')->unique();
            $table->string('qr_token', 40)->unique(); $table->string('pdf_path')->nullable();
            $table->enum('pdf_status', ['pending','queued','generated','failed'])->default('pending'); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payment_receipts'); }
};
