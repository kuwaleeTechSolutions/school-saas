<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('impersonation_sessions', function (Blueprint $table) {
            $table->id(); $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->unsignedBigInteger('impersonated_user_id');
            $table->text('reason'); $table->timestamp('started_at'); $table->timestamp('ended_at')->nullable();
            $table->string('ip_address', 45)->nullable(); $table->timestamps(); $table->index(['admin_id','school_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('impersonation_sessions'); }
};
