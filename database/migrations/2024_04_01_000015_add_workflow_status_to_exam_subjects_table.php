<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->enum('status', ['pending','entered','submitted','class_teacher_verified','coordinator_verified','principal_approved','published'])->default('pending')->after('pass_marks');
        });
    }
    public function down(): void { Schema::table('exam_subjects', function (Blueprint $table) { $table->dropColumn('status'); }); }
};
