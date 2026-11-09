<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_draft_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('semester_id')->constrained()->restrictOnDelete();
            $table->foreignId('published_by_user_id')->constrained('users')->restrictOnDelete();
            $table->json('snapshot');
            $table->string('pdf_path');
            $table->timestamp('published_at');
            $table->timestamps();

            $table->unique('report_draft_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_publications');
    }
};
