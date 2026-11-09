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
        // 1. Aspek Penilaian Sikap (Configurable Aspects)
        Schema::create('attitude_aspects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Penilaian Sikap Siswa per Aspek & Semester
        Schema::create('student_attitude_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('attitude_aspect_id')->constrained('attitude_aspects')->cascadeOnDelete();
            $table->string('rating'); // 'Sangat Baik', 'Baik', 'Cukup', 'Perlu Bimbingan'
            $table->text('development_notes')->nullable();
            $table->foreignId('assessed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'semester_id', 'attitude_aspect_id'], 'unique_student_semester_aspect_attitude');
        });

        // Catatan Rangkuman Sikap Tambahan per Siswa & Semester
        Schema::create('student_attitude_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->text('general_notes')->nullable();
            $table->foreignId('assessed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'semester_id'], 'unique_student_semester_attitude_summary');
        });

        // 3. Kehadiran Siswa (Harian & Rekap Semester)
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->date('date');
            $table->string('status'); // 'Hadir', 'Sakit', 'Izin', 'Alpa'
            $table->string('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Mencegah kehadiran ganda untuk satu siswa pada tanggal dan semester yang sama
            $table->unique(['student_id', 'date', 'semester_id'], 'unique_student_date_semester_attendance');
        });

        // 4. Keaktifan Belajar Siswa
        Schema::create('student_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->date('date');
            $table->string('aspect'); // 'Keaktifan Bertanya', 'Partisipasi Diskusi', 'Keterlibatan Kegiatan Kelas', 'Kerja Sama Kelompok', 'Inisiatif Belajar'
            $table->text('description');
            $table->text('teacher_notes')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Master Kegiatan Ekstrakurikuler
        Schema::create('extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->default('Umum'); // 'Keagamaan', 'Seni & Budaya', 'Olahraga', 'Kepanduan', 'Akademik'
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Keikutsertaan Ekstrakurikuler Siswa per Semester
        Schema::create('student_extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->string('predicate'); // 'Sangat Baik', 'Baik', 'Cukup'
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'semester_id', 'extracurricular_id'], 'unique_student_semester_extracurricular');
        });

        // 6. Prestasi Siswa
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->string('achievement_name');
            $table->string('level'); // 'Madrasah', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional'
            $table->string('activity_type'); // 'Keagamaan', 'Akademik', 'Seni & Budaya', 'Olahraga'
            $table->string('rank'); // 'Juara 1', 'Juara 2', 'Juara 3', 'Harapan 1', dll.
            $table->string('organizer');
            $table->date('date')->nullable();
            $table->text('notes')->nullable();
            $table->string('supporting_document')->nullable();
            $table->timestamps();
        });

        // 7. Catatan Wali Kelas
        Schema::create('homeroom_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->text('academic_progress')->nullable();
            $table->text('attitude_behavior')->nullable();
            $table->text('discipline')->nullable();
            $table->text('student_potential')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('parent_advice')->nullable();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'semester_id'], 'unique_student_semester_homeroom_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homeroom_notes');
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('student_extracurriculars');
        Schema::dropIfExists('extracurriculars');
        Schema::dropIfExists('student_activities');
        Schema::dropIfExists('student_attendances');
        Schema::dropIfExists('student_attitude_summaries');
        Schema::dropIfExists('student_attitude_ratings');
        Schema::dropIfExists('attitude_aspects');
    }
};
