<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('library_book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('library_book_id')->constrained('library_books')->cascadeOnDelete();
            $table->string('accession_number')->unique();
            $table->enum('condition', ['good','worn','damaged','lost'])->default('good');
            $table->enum('status', ['available','issued','lost','retired'])->default('available');
            $table->timestamps();
            $table->index(['school_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('library_book_copies'); }
};
