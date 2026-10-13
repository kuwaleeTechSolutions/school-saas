<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('plans', function (Blueprint $table) {
            $table->id(); $table->string('ulid', 26)->unique(); $table->string('name'); $table->string('code')->unique();
            $table->text('description')->nullable(); $table->string('badge')->nullable(); $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_recommended')->default(false); $table->enum('status', ['draft','active','inactive'])->default('draft');
            $table->unsignedInteger('trial_days')->default(0); $table->timestamps(); $table->softDeletes(); $table->index(['status','display_order']);
        });
    }
    public function down(): void { Schema::dropIfExists('plans'); }
};
