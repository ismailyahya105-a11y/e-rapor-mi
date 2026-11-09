<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\HomeroomNote;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeroomNoteController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('school_class_id');
        $search = $request->query('q');

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

        $students = collect();
        $existingNotes = [];

        if ($selectedSemester && $selectedClass) {
            $enrollmentsQuery = Enrollment::query()
                ->where('semester_id', $selectedSemester->id)
                ->where('school_class_id', $selectedClass->id)
                ->with('student');

            if ($search) {
                $enrollmentsQuery->whereHas('student', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            }

            $students = $enrollmentsQuery->get()->pluck('student')->sortBy('name');

            $notes = HomeroomNote::query()
                ->where('semester_id', $selectedSemester->id)
                ->where('school_class_id', $selectedClass->id)
                ->get()
                ->keyBy('student_id');

            $existingNotes = $notes;
        }

        return view('admin.homeroom-notes.index', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'students' => $students,
            'existingNotes' => $existingNotes,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'semester_id' => ['required', 'exists:semesters,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'notes' => ['required', 'array'],
        ]);

        $semester = Semester::query()->findOrFail($validated['semester_id']);
        if ($semester->isLocked()) {
            return back()->with('error', 'Semester ini telah dikunci/diarsipkan. Catatan wali kelas tidak dapat diubah.');
        }

        $semesterId = $validated['semester_id'];
        $classId = $validated['school_class_id'];
        $notesInput = $request->input('notes', []);
        $userId = auth()->id();

        $validStudentIds = Enrollment::query()
            ->where('semester_id', $semesterId)
            ->where('school_class_id', $classId)
            ->pluck('student_id')
            ->flip()
            ->toArray();

        DB::transaction(function () use ($semesterId, $classId, $notesInput, $validStudentIds, $userId) {
            foreach ($notesInput as $studentId => $data) {
                if (! isset($validStudentIds[$studentId])) {
                    abort(403, 'Akses ditolak: Siswa tidak terdaftar di kelas dan semester ini.');
                }

                $academic = trim((string) ($data['academic_progress'] ?? ''));
                $attitude = trim((string) ($data['attitude_behavior'] ?? ''));
                $discipline = trim((string) ($data['discipline'] ?? ''));
                $potential = trim((string) ($data['student_potential'] ?? ''));
                $improvement = trim((string) ($data['areas_for_improvement'] ?? ''));
                $parentAdvice = trim((string) ($data['parent_advice'] ?? ''));

                // Jika ada minimal satu kolom catatan yang terisi
                if ($academic !== '' || $attitude !== '' || $discipline !== '' || $potential !== '' || $improvement !== '' || $parentAdvice !== '') {
                    HomeroomNote::query()->updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'semester_id' => $semesterId,
                        ],
                        [
                            'school_class_id' => $classId,
                            'academic_progress' => $academic ?: null,
                            'attitude_behavior' => $attitude ?: null,
                            'discipline' => $discipline ?: null,
                            'student_potential' => $potential ?: null,
                            'areas_for_improvement' => $improvement ?: null,
                            'parent_advice' => $parentAdvice ?: null,
                            'author_user_id' => $userId,
                        ]
                    );
                }
            }
        });

        return redirect()->route('admin.homeroom-notes.index', [
            'semester_id' => $semesterId,
            'school_class_id' => $classId,
        ])->with('status', 'Catatan wali kelas berhasil disimpan.');
    }
}
