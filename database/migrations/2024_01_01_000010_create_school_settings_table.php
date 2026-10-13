<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->string('category');
            $table->string('key'); $table->text('value')->nullable(); $table->timestamps();
            $table->unique(['school_id','category','key']); $table->index(['school_id','category']);
        });
    }
    public function down(): void { Schema::dropIfExists('school_settings'); }
};
