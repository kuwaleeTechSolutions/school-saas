<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('admission_application_documents', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('admission_application_id')->constrained('admission_applications')->cascadeOnDelete(); $table->string('document_type');
            $table->string('file_path'); $table->string('original_name'); $table->boolean('is_verified')->default(false);
            $table->timestamps(); $table->index(['school_id','admission_application_id'], 'admission_docs_school_app_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('admission_application_documents'); }
};
