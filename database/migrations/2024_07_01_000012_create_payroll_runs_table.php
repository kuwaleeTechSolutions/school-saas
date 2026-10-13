<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->enum('status', ['draft','finalized'])->default('draft');
            $table->unsignedBigInteger('generated_by');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'year', 'month']);
        });
    }
    public function down(): void { Schema::dropIfExists('payroll_runs'); }
};
