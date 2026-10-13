<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id(); $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->decimal('discount_applied',10,2); $table->timestamp('redeemed_at'); $table->timestamps(); $table->index(['coupon_id','school_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('coupon_redemptions'); }
};
