<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('schools', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->string('school_code')->unique(); $table->string('name');
            $table->string('slug')->unique(); $table->string('email')->unique(); $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable(); $table->string('website')->nullable(); $table->string('logo')->nullable();
            $table->string('address')->nullable(); $table->string('city')->nullable(); $table->string('district')->nullable();
            $table->string('state')->nullable(); $table->string('country')->default('India'); $table->string('pincode')->nullable();
            $table->string('board')->nullable(); $table->string('affiliation_number')->nullable(); $table->string('udise_code')->nullable();
            $table->unsignedSmallInteger('established_year')->nullable(); $table->string('principal_name')->nullable();
            $table->enum('status', ['pending','trial','active','past_due','grace_period','suspended','cancelled','expired'])->default('pending');
            $table->enum('onboarding_status', ['not_started','in_progress','completed'])->default('not_started');
            $table->string('timezone')->default('Asia/Kolkata'); $table->string('currency', 3)->default('INR');
            $table->foreignId('current_academic_session_id')->nullable(); $table->timestamps(); $table->softDeletes();
            $table->index('status'); $table->index(['status','onboarding_status']);
        });
    }
    public function down(): void { Schema::dropIfExists('schools'); }
};
