<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('fee_installment_plans', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete(); $table->string('name');
            $table->enum('frequency', ['annual','half_yearly','quarterly','monthly','custom'])->default('annual'); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('fee_installment_plans'); }
};
