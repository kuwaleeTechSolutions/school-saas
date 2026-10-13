<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable()->after('guard_name')->constrained('modules')->nullOnDelete();
            $table->foreignId('feature_id')->nullable()->after('module_id')->constrained('features')->nullOnDelete();
            $table->string('action')->nullable()->after('feature_id'); $table->index(['module_id','feature_id']);
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('guard_name')->constrained('schools')->cascadeOnDelete(); $table->index('school_id');
        });
    }
    public function down(): void {
        Schema::table('roles', function (Blueprint $table) { $table->dropConstrainedForeignId('school_id'); });
        Schema::table('permissions', function (Blueprint $table) { $table->dropConstrainedForeignId('module_id'); $table->dropConstrainedForeignId('feature_id'); $table->dropColumn('action'); });
    }
};
