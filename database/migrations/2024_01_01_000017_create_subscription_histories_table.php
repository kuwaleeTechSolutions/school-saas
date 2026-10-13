<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subscription_histories', function (Blueprint $table) {
            $table->id(); $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete(); $table->string('change_type');
            $table->json('old_values')->nullable(); $table->json('new_values')->nullable(); $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable(); $table->text('reason')->nullable(); $table->timestamps(); $table->index('subscription_id');
        });
    }
    public function down(): void { Schema::dropIfExists('subscription_histories'); }
};
