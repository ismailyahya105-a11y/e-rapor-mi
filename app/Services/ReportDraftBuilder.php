<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\HomeroomNote;
use App\Models\HomeroomTeacherAssignment;
use App\Models\ReportDraft;
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentActivity;
use App\Models\StudentAttendance;
use App\Models\StudentAttitudeRating;
use App\Models\StudentAttitudeSummary;
use App\Models\StudentExtracurricular;
use App\Models\StudentSubjectResult;
use App\Models\SubjectAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ReportDraftBuilder
{
    /** @return array<string, mixed> */
    public function build(Student $student, Semester $semester): array
    {
        $enrollment = Enrollment::query()->with('schoolClass.academicYear')->where('student_id', $student->id)->where('semester_id', $semester->id)->first();
        if (! $enrollment) {
            throw new RuntimeException('Siswa tidak terdaftar pada semester yang dipilih.');
        }

        $assignments = SubjectAssignment::query()->where('semester_id', $semester->id)->where('school_class_id', $enrollment->school_class_id)->with('subject')->get();
        $results = StudentSubjectResult::query()->where('student_id', $student->id)->whereIn('subject_assignment_id', $assignments->pluck('id'))->get()->keyBy('subject_assignment_id');
        $profile = SchoolProfile::query()->first();
        $teacher = HomeroomTeacherAssignment::query()->where('semester_id', $semester->id)->where('school_class_id', $enrollment->school_class_id)->first();
        $attendances = StudentAttendance::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->get();

        $school = $profile?->only(['name', 'npsn', 'nsm', 'address', 'village', 'district', 'regency', 'province', 'principal_name', 'principal_nip']) ?? [];
        $school['logo'] = $profile?->logo_path && Storage::disk('public')->exists($profile->logo_path)
            ? base64_encode(Storage::disk('public')->get($profile->logo_path)) : null;

        return [
            'school' => $school,
            'student' => $student->only(['name', 'nis', 'nisn', 'gender', 'birth_place', 'birth_date', 'address']),
            'enrollment' => ['class_name' => $enrollment->schoolClass->name, 'grade_level' => $enrollment->schoolClass->grade_level, 'semester' => $semester->name, 'academic_year' => $enrollment->schoolClass->academicYear->name],
            'signatories' => ['homeroom_teacher' => $teacher?->only(['teacher_name', 'teacher_nip']), 'principal' => $profile?->only(['principal_name', 'principal_nip'])],
            'academics' => $assignments->map(fn (SubjectAssignment $assignment): array => ['subject' => $assignment->subject->name, 'final_score' => $results->get($assignment->id)?->final_score, 'description' => $results->get($assignment->id)?->competency_description, 'is_complete' => $results->get($assignment->id)?->is_complete ?? false])->values()->all(),
            'attitudes' => StudentAttitudeRating::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->with('aspect')->get()->map(fn ($rating): array => ['aspect' => $rating->aspect->name, 'rating' => $rating->rating, 'notes' => $rating->development_notes])->all(),
            'attitude_summary' => StudentAttitudeSummary::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->value('general_notes'),
            'attendance' => ['hadir' => $attendances->where('status', 'Hadir')->count(), 'sakit' => $attendances->where('status', 'Sakit')->count(), 'izin' => $attendances->where('status', 'Izin')->count(), 'alpa' => $attendances->where('status', 'Alpa')->count(), 'total' => $attendances->count()],
            'activities' => StudentActivity::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->get(['date', 'aspect', 'description', 'teacher_notes'])->all(),
            'extracurriculars' => StudentExtracurricular::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->with('extracurricular')->get()->map(fn ($item): array => ['name' => $item->extracurricular->name, 'predicate' => $item->predicate, 'description' => $item->description])->all(),
            'achievements' => StudentAchievement::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->get(['achievement_name', 'level', 'rank', 'organizer', 'date'])->all(),
            'homeroom_note' => HomeroomNote::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->first()?->only(['academic_progress', 'attitude_behavior', 'discipline', 'student_potential', 'areas_for_improvement', 'parent_advice']),
        ];
    }

    public function refresh(Student $student, Semester $semester, User $updatedBy): ReportDraft
    {
        return DB::transaction(function () use ($student, $semester, $updatedBy): ReportDraft {
            $snapshot = $this->build($student, $semester);
            $enrollment = Enrollment::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->firstOrFail();
            $draft = ReportDraft::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->lockForUpdate()->first();

            return ReportDraft::query()->updateOrCreate(['student_id' => $student->id, 'semester_id' => $semester->id], ['enrollment_id' => $enrollment->id, 'updated_by_user_id' => $updatedBy->id, 'snapshot' => $snapshot, 'source_fingerprint' => hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR))]);
        });
    }
}
