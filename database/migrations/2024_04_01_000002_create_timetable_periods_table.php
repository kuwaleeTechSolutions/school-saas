<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->string('name');
            $table->unsignedTinyInteger('sort_order'); $table->time('starts_at'); $table->time('ends_at');
            $table->boolean('is_teaching_period')->default(true); $table->timestamps(); $table->unique(['school_id','sort_order']);
        });
    }
    public function down(): void { Schema::dropIfExists('timetable_periods'); }
};
