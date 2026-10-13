<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans'); $table->enum('billing_cycle', ['monthly','quarterly','half_yearly','annual']);
            $table->enum('status', ['pending','trial','active','past_due','grace_period','suspended','cancelled','expired','scheduled_cancellation'])->default('pending');
            $table->timestamp('trial_ends_at')->nullable(); $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable(); $table->timestamp('grace_period_ends_at')->nullable();
            $table->timestamp('cancellation_effective_at')->nullable(); $table->boolean('is_active')->default(false);
            $table->timestamps(); $table->index(['school_id','is_active']); $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); }
};
