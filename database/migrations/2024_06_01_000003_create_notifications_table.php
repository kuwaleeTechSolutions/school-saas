<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Section 41 — "Trigger configurable notification" (referenced back in
     * Stage 3/4's StudentMarkedAbsent / exam-publish events). One row per
     * (recipient, event) regardless of how many channels it goes out on —
     * `notification_deliveries` tracks the per-channel attempt/result
     * separately, so "was this read in-app" and "did the SMS actually send"
     * are never conflated into one status column.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id'); // tenant users.id — the recipient
            $table->string('type'); // e.g. "attendance.absent", "fees.invoice_due", "results.published", "announcement.published"
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable(); // e.g. {"student_id": 5, "invoice_id": 12} for deep-linking
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'user_id', 'read_at']);
            $table->index(['school_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
