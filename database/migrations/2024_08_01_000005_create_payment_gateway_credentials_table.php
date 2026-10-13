<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payment_gateway_credentials', function (Blueprint $table) {
            $table->id(); $table->string('provider')->unique(); $table->text('api_key')->nullable(); $table->text('api_secret')->nullable();
            $table->text('webhook_secret')->nullable(); $table->boolean('is_active')->default(false); $table->boolean('is_test_mode')->default(true); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payment_gateway_credentials'); }
};
