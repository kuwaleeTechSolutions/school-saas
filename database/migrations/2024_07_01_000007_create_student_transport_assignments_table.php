<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('student_transport_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions');
            $table->foreignId('transport_route_id')->constrained('transport_routes');
            $table->foreignId('transport_stop_id')->nullable()->constrained('transport_stops')->nullOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'academic_session_id'], 'student_transport_session_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('student_transport_assignments'); }
};
