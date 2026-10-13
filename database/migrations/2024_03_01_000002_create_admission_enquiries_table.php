<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('admission_enquiries', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('child_name'); $table->string('guardian_name'); $table->string('phone'); $table->string('email')->nullable();
            $table->string('interested_class')->nullable(); $table->text('notes')->nullable();
            $table->enum('status', ['new','contacted','converted','closed'])->default('new'); $table->timestamps(); $table->index(['school_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('admission_enquiries'); }
};
