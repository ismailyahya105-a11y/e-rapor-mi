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
        Schema::create('assessment_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight', 5, 2);
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['subject_id', 'semester_id', 'name']);
            $table->index(['subject_id', 'semester_id']);
        });

        Schema::create('student_assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_assignment_id')->constrained('subject_assignments')->cascadeOnDelete();
            $table->foreignId('assessment_component_id')->constrained('assessment_components')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_assignment_id', 'assessment_component_id'], 'student_assignment_component_unique');
            $table->index(['student_id', 'subject_assignment_id']);
        });

        Schema::create('student_subject_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_assignment_id')->constrained('subject_assignments')->cascadeOnDelete();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->text('competency_description')->nullable();
            $table->boolean('is_complete')->default(false)->index();
            $table->timestamps();

            $table->unique(['student_id', 'subject_assignment_id']);
            $table->index(['subject_assignment_id', 'is_complete']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_subject_results');
        Schema::dropIfExists('student_assessment_scores');
        Schema::dropIfExists('assessment_components');
    }
};
