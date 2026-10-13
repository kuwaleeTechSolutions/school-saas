<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('library_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('library_book_copy_id')->constrained('library_book_copies')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('issued_on');
            $table->date('due_on');
            $table->date('returned_on')->nullable();
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->boolean('fine_waived')->default(false);
            $table->unsignedBigInteger('issued_by');
            $table->timestamps();
            $table->index(['school_id', 'student_id']);
            $table->index(['school_id', 'returned_on']);
        });
    }
    public function down(): void { Schema::dropIfExists('library_issues'); }
};
