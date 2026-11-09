<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubjectAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('school_class_id');

        $semesters = Semester::query()->with('academicYear')->orderBy('id', 'desc')->get();

        $selectedSemester = $selectedSemesterId
            ? Semester::query()->with('academicYear')->find($selectedSemesterId)
            : $activeSemester;

        $availableClasses = $selectedSemester
            ? SchoolClass::query()->where('academic_year_id', $selectedSemester->academic_year_id)->orderBy('grade_level')->orderBy('name')->get()
            : collect();

        $selectedClass = $selectedClassId
            ? SchoolClass::query()->find($selectedClassId)
            : $availableClasses->first();

        $assignments = collect();
        $unassignedSubjects = collect();

        if ($selectedSemester && $selectedClass) {
            $assignments = SubjectAssignment::query()
                ->where('semester_id', $selectedSemester->id)
                ->where('school_class_id', $selectedClass->id)
                ->with('subject')
                ->get();

            $assignedSubjectIds = $assignments->pluck('subject_id');

            $unassignedSubjects = Subject::query()
                ->where('is_active', true)
                ->whereNotIn('id', $assignedSubjectIds)
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        }

        return view('admin.subject-assignments.index', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'assignments' => $assignments,
            'unassignedSubjects' => $unassignedSubjects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'semester_id' => ['required', 'exists:semesters,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['exists:subjects,id'],
        ], [
            'semester_id.required' => 'Semester wajib dipilih.',
            'school_class_id.required' => 'Kelas wajib dipilih.',
            'subject_ids.required' => 'Pilih minimal satu mata pelajaran untuk ditugaskan.',
        ]);

        $semester = Semester::query()->with('academicYear')->findOrFail($validated['semester_id']);
        $schoolClass = SchoolClass::query()->with('academicYear')->findOrFail($validated['school_class_id']);

        if ($schoolClass->academic_year_id !== $semester->academic_year_id) {
            throw ValidationException::withMessages([
                'school_class_id' => "Tahun ajaran kelas ({$schoolClass->academicYear->name}) tidak sesuai dengan tahun ajaran semester ({$semester->academicYear->name}).",
            ]);
        }

        foreach ($validated['subject_ids'] as $subjectId) {
            SubjectAssignment::query()->firstOrCreate([
                'semester_id' => $semester->id,
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subjectId,
            ]);
        }

        return redirect()->route('admin.subject-assignments.index', [
            'semester_id' => $semester->id,
            'school_class_id' => $schoolClass->id,
        ])->with('status', 'Mata pelajaran berhasil ditugaskan ke kelas.');
    }

    public function destroy(SubjectAssignment $subjectAssignment): RedirectResponse
    {
        $semesterId = $subjectAssignment->semester_id;
        $classId = $subjectAssignment->school_class_id;

        $subjectAssignment->delete();

        return redirect()->route('admin.subject-assignments.index', [
            'semester_id' => $semesterId,
            'school_class_id' => $classId,
        ])->with('status', 'Penugasan mata pelajaran berhasil dihapus dari kelas.');
    }
}
