<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('admission_form_configs', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->json('field_config')->nullable();
            $table->json('required_documents')->nullable(); $table->decimal('application_fee', 10, 2)->default(0); $table->timestamps(); $table->unique('school_id');
        });
    }
    public function down(): void { Schema::dropIfExists('admission_form_configs'); }
};
