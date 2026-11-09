<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\Student;
use App\Models\SubjectAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildController extends Controller
{
    public function show(Request $request, Student $student): View
    {
        // Pembatasan server: pastikan siswa benar-benar terhubung dengan wali yang sedang login
        $isLinked = $request->user()->students()->where('students.id', $student->id)->exists();

        abort_unless($isLinked, 403, 'Akses ditolak: Anda tidak memiliki wewenang untuk melihat data anak ini.');

        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();

        $student->load([
            'enrollments.schoolClass.academicYear',
            'enrollments.semester.academicYear',
            'guardians',
            'reportPublications' => fn ($q) => $q->with('semester.academicYear')->latest('published_at'),
        ]);

        $currentEnrollment = $student->currentEnrollment($activeSemester);
        $currentClass = $currentEnrollment?->schoolClass;

        $subjects = collect();
        if ($activeSemester && $currentClass) {
            $subjects = SubjectAssignment::query()
                ->where('semester_id', $activeSemester->id)
                ->where('school_class_id', $currentClass->id)
                ->with('subject')
                ->get()
                ->pluck('subject');
        }

        return view('wali.child-detail', [
            'student' => $student,
            'activeSemester' => $activeSemester,
            'currentEnrollment' => $currentEnrollment,
            'currentClass' => $currentClass,
            'subjects' => $subjects,
        ]);
    }
}
