<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ulid', 26)->unique()->after('id'); $table->foreignId('school_id')->nullable()->after('ulid')->constrained('schools')->cascadeOnDelete();
            $table->string('phone')->nullable()->after('email'); $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('password'); $table->enum('status', ['active','inactive','suspended'])->default('active')->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('status'); $table->softDeletes(); $table->index(['school_id','status']);
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) { $table->dropConstrainedForeignId('school_id'); $table->dropColumn(['ulid','phone','phone_verified_at','avatar','status','last_login_at']); $table->dropSoftDeletes(); });
    }
};
