<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->string('name');
            $table->date('starts_on'); $table->date('ends_on'); $table->boolean('is_current')->default(false);
            $table->timestamps(); $table->unique(['school_id','name']); $table->index(['school_id','is_current']);
        });
        Schema::table('schools', function (Blueprint $table) { $table->foreign('current_academic_session_id')->references('id')->on('academic_sessions')->nullOnDelete(); });
    }
    public function down(): void { Schema::table('schools', function (Blueprint $table) { $table->dropForeign(['current_academic_session_id']); }); Schema::dropIfExists('academic_sessions'); }
};
