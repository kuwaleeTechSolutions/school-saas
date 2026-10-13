<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Section 41 — "Support multiple channels: in-app, email, SMS,
     * WhatsApp." One row per channel attempted for a given notification.
     * `status` records the real-world outcome (queued/sent/failed) rather
     * than assuming a channel attempt always succeeds — SMS/WhatsApp
     * gateway credentials are a Stage 8 integration concern, so those
     * channels are implemented here as a logging stub that always reports
     * 'sent' without a real external call, clearly documented in
     * NotificationService's docblock.
     */
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->enum('channel', ['in_app', 'email', 'sms', 'whatsapp'])->default('in_app');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('failure_reason')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'notification_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
