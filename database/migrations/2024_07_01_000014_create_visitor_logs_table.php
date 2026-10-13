<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('visitor_name');
            $table->string('visitor_phone')->nullable();
            $table->string('purpose');
            $table->string('whom_to_meet')->nullable();
            $table->string('gate_pass_number')->unique();
            $table->timestamp('checked_in_at');
            $table->timestamp('checked_out_at')->nullable();
            $table->unsignedBigInteger('logged_by');
            $table->timestamps();
            $table->index(['school_id', 'checked_in_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('visitor_logs'); }
};
