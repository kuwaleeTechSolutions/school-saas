<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id(); $table->string('ulid',26)->unique(); $table->string('code')->unique(); $table->string('description')->nullable();
            $table->enum('discount_type',['percentage','fixed_amount'])->default('percentage'); $table->decimal('value',10,2);
            $table->unsignedInteger('max_redemptions')->nullable(); $table->unsignedInteger('max_redemptions_per_school')->default(1);
            $table->date('valid_from')->nullable(); $table->date('valid_until')->nullable(); $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by'); $table->timestamps(); $table->softDeletes(); $table->index(['code','is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('coupons'); }
};
