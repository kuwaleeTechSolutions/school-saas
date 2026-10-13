<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->enum('category', ['academic','facility','behavioral','transport','fees','other'])->default('other');
            $table->enum('status', ['open','in_progress','resolved','closed'])->default('open');
            $table->unsignedBigInteger('raised_by');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'raised_by']);
        });
    }
    public function down(): void { Schema::dropIfExists('complaints'); }
};
