<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('name'); $table->string('phone')->nullable();
            $table->string('email')->nullable(); $table->string('occupation')->nullable(); $table->string('address')->nullable();
            $table->timestamps(); $table->softDeletes(); $table->index('school_id');
        });
    }
    public function down(): void { Schema::dropIfExists('guardians'); }
};
