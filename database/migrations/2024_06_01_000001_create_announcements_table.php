<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Section 41 — "Announcements: school-wide or targeted by
     * class/section/role." `audience_type` + `audience_filters` describe
     * WHO sees it; AnnouncementService resolves the actual recipient list
     * from this at publish time, never from a pre-materialized list, so an
     * announcement's audience stays correct even as enrollment changes.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->enum('audience_type', ['school_wide', 'class', 'section', 'role'])->default('school_wide');
            $table->json('audience_filters')->nullable(); // e.g. {"class_id": 3} or {"section_id": 7} or {"role": "Parent"}
            $table->unsignedBigInteger('published_by'); // users.id
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
