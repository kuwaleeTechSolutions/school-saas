<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete(); $table->string('name');
            $table->enum('discount_type', ['percentage','fixed_amount'])->default('percentage'); $table->decimal('value', 10, 2);
            $table->boolean('is_active')->default(true); $table->timestamps(); $table->unique(['school_id','name']);
        });
    }
    public function down(): void { Schema::dropIfExists('scholarships'); }
};
