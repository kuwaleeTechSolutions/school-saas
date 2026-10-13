<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subscription_events', function (Blueprint $table) {
            $table->id(); $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete(); $table->string('event_type');
            $table->json('payload')->nullable(); $table->timestamp('occurred_at'); $table->timestamps(); $table->index(['subscription_id','event_type']);
        });
    }
    public function down(): void { Schema::dropIfExists('subscription_events'); }
};
