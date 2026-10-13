<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('type', ['birth_certificate','aadhaar','previous_marksheet','transfer_certificate','address_proof','medical_certificate','photograph','other']);
            $table->string('file_path'); $table->string('original_name'); $table->foreignId('uploaded_by')->constrained('users');
            $table->date('expires_on')->nullable(); $table->timestamps(); $table->index(['school_id','student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('student_documents'); }
};
