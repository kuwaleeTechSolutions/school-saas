<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->date('holiday_date');
            $table->string('title'); $table->timestamps(); $table->unique(['school_id','holiday_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('holidays'); }
};
