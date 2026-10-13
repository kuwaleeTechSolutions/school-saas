<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('issued_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('certificate_template_id')->constrained('certificate_templates');
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('certificate_number')->unique();
            $table->string('qr_token', 40)->unique();
            $table->text('rendered_body');
            $table->unsignedBigInteger('issued_by');
            $table->date('issued_on');
            $table->string('pdf_path')->nullable();
            $table->enum('pdf_status', ['pending','queued','generated','failed'])->default('pending');
            $table->timestamps();
            $table->index(['school_id', 'student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('issued_certificates'); }
};
