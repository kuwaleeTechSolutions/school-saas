<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('houses', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->string('name');
            $table->string('color_code', 7)->nullable(); $table->timestamps(); $table->unique(['school_id','name']);
        });
    }
    public function down(): void { Schema::dropIfExists('houses'); }
};
