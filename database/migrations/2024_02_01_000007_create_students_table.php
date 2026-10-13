<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('students', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('admission_number');
            $table->string('first_name'); $table->string('middle_name')->nullable(); $table->string('last_name')->nullable();
            $table->enum('gender', ['male','female','other'])->nullable(); $table->date('date_of_birth')->nullable(); $table->string('photo')->nullable();
            $table->string('blood_group', 5)->nullable(); $table->string('nationality')->default('Indian'); $table->string('religion')->nullable();
            $table->string('category')->nullable(); $table->string('phone')->nullable(); $table->string('email')->nullable();
            $table->string('address')->nullable(); $table->string('city')->nullable(); $table->string('district')->nullable();
            $table->string('state')->nullable(); $table->string('pincode')->nullable(); $table->date('admission_date')->nullable();
            $table->foreignId('house_id')->nullable()->constrained('houses')->nullOnDelete(); $table->string('previous_school')->nullable();
            $table->text('transfer_details')->nullable(); $table->text('allergies')->nullable(); $table->text('medical_notes')->nullable();
            $table->string('emergency_contact_name')->nullable(); $table->string('emergency_contact_phone')->nullable();
            $table->enum('status', ['active','inactive','alumni','transferred_out'])->default('active'); $table->timestamps(); $table->softDeletes();
            $table->unique(['school_id','admission_number']); $table->index(['school_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('students'); }
};
