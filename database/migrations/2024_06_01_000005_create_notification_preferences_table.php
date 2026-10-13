<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Section 41 — lets a user opt out of a channel for a notification
     * type (e.g. "don't SMS me about homework, only email"). Absence of a
     * row for (user, type, channel) means "enabled" — NotificationService
     * only checks this table for an explicit opt-out, so new notification
     * types never require a migration to seed default preferences.
     */
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->string('notification_type');
            $table->enum('channel', ['in_app', 'email', 'sms', 'whatsapp']);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'notification_type', 'channel'], 'notif_pref_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
