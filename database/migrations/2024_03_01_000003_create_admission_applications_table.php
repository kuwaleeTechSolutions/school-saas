<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('admission_enquiry_id')->nullable()->constrained('admission_enquiries')->nullOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions'); $table->string('applicant_first_name');
            $table->string('applicant_last_name')->nullable(); $table->date('applicant_date_of_birth')->nullable();
            $table->enum('applicant_gender', ['male','female','other'])->nullable(); $table->string('guardian_name'); $table->string('guardian_phone');
            $table->string('guardian_email')->nullable(); $table->string('applying_for_class'); $table->string('previous_school')->nullable();
            $table->enum('stage', ['enquiry','application','submitted','under_review','document_verification','entrance_test','interview','selected','waitlisted','rejected','fee_pending','admitted','cancelled'])->default('application');
            $table->decimal('application_fee_amount', 10, 2)->default(0); $table->boolean('application_fee_paid')->default(false);
            $table->decimal('entrance_test_score', 6, 2)->nullable(); $table->text('interview_notes')->nullable(); $table->text('rejection_reason')->nullable();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete(); $table->timestamps(); $table->softDeletes();
            $table->index(['school_id','stage']); $table->index(['school_id','academic_session_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('admission_applications'); }
};
