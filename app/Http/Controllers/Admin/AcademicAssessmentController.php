<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentComponent;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentAssessmentScore;
use App\Models\StudentSubjectResult;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AcademicAssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('school_class_id');
        $selectedSubjectId = $request->query('subject_id');
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

        // Cari mata pelajaran yang ditugaskan ke kelas dan semester terpilih
        $assignedSubjects = collect();
        if ($selectedSemester && $selectedClass) {
            $assignedSubjects = Subject::query()
                ->whereHas('subjectAssignments', function ($q) use ($selectedSemester, $selectedClass) {
                    $q->where('semester_id', $selectedSemester->id)
                        ->where('school_class_id', $selectedClass->id);
                })
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        }

        $selectedSubject = $selectedSubjectId
            ? Subject::query()->find($selectedSubjectId)
            : $assignedSubjects->first();

        $assignment = null;
        $components = collect();
        $totalWeight = 0;
        $isWeightReady = false;
        $students = collect();
        $existingScores = [];
        $existingResults = [];

        if ($selectedSemester && $selectedClass && $selectedSubject) {
            $assignment = SubjectAssignment::query()
                ->where('semester_id', $selectedSemester->id)
                ->where('school_class_id', $selectedClass->id)
                ->where('subject_id', $selectedSubject->id)
                ->first();

            if ($assignment) {
                // Komponen penilaian aktif untuk mapel & semester ini
                $components = AssessmentComponent::query()
                    ->where('subject_id', $selectedSubject->id)
                    ->where('semester_id', $selectedSemester->id)
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->orderBy('id')
                    ->get();

                $totalWeight = (float) $components->sum('weight');
                $isWeightReady = abs($totalWeight - 100.0) < 0.001;

                // Ambil seluruh siswa yang ditempatkan pada kelas & semester ini
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

                $enrollments = $enrollmentsQuery->get();
                $students = $enrollments->pluck('student')->sortBy('name');

                // Ambil nilai komponen yang tersimpan
                $scores = StudentAssessmentScore::query()
                    ->where('subject_assignment_id', $assignment->id)
                    ->get();

                foreach ($scores as $sc) {
                    $existingScores[$sc->student_id][$sc->assessment_component_id] = $sc->score;
                }

                // Ambil hasil akhir dan deskripsi capaian yang tersimpan
                $results = StudentSubjectResult::query()
                    ->where('subject_assignment_id', $assignment->id)
                    ->get();

                foreach ($results as $res) {
                    $existingResults[$res->student_id] = $res;
                }
            }
        }

        return view('admin.assessments.index', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'assignedSubjects' => $assignedSubjects,
            'selectedSubject' => $selectedSubject,
            'assignment' => $assignment,
            'components' => $components,
            'totalWeight' => $totalWeight,
            'isWeightReady' => $isWeightReady,
            'students' => $students,
            'existingScores' => $existingScores,
            'existingResults' => $existingResults,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_assignment_id' => ['required', 'exists:subject_assignments,id'],
            'scores' => ['nullable', 'array'],
            'descriptions' => ['nullable', 'array'],
        ], [
            'subject_assignment_id.required' => 'Penugasan mata pelajaran tidak valid.',
        ]);

        $assignment = SubjectAssignment::query()
            ->with(['schoolClass', 'semester.academicYear', 'subject'])
            ->findOrFail($validated['subject_assignment_id']);

        if ($assignment->semester->isLocked()) {
            return back()->with('error', 'Semester ini telah dikunci/diarsipkan. Penilaian akademik tidak dapat diubah.');
        }

        // Komponen aktif untuk mapel & semester ini
        $components = AssessmentComponent::query()
            ->where('subject_id', $assignment->subject_id)
            ->where('semester_id', $assignment->semester_id)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        if ($components->isEmpty()) {
            throw ValidationException::withMessages([
                'scores' => 'Komponen penilaian untuk mata pelajaran ini belum diatur. Silakan atur komponen terlebih dahulu.',
            ]);
        }

        // Ambil daftar valid ID siswa yang terdaftar di kelas & semester ini
        $validStudentIds = Enrollment::query()
            ->where('semester_id', $assignment->semester_id)
            ->where('school_class_id', $assignment->school_class_id)
            ->pluck('student_id')
            ->flip()
            ->toArray();

        $scoresInput = $request->input('scores', []);
        $descriptionsInput = $request->input('descriptions', []);

        $completedCount = 0;
        $incompleteCount = 0;

        DB::transaction(function () use ($assignment, $components, $validStudentIds, $scoresInput, $descriptionsInput, &$completedCount, &$incompleteCount) {
            foreach ($scoresInput as $studentId => $studentScores) {
                // Keamanan: Validasi bahwa siswa benar-benar terdaftar di kelas dan semester ini
                if (! isset($validStudentIds[$studentId])) {
                    abort(403, 'Akses ditolak: Percobaan memanipulasi penilaian untuk siswa dari kelas lain.');
                }

                $studentAllComponentsFilled = true;
                $weightedScoreSum = 0.0;
                $activeComponentCount = $components->count();

                foreach ($components as $component) {
                    $rawScore = $studentScores[$component->id] ?? null;

                    // Perbedaan nilai kosong vs nol:
                    // Jika null atau string kosong, disimpan sebagai null
                    if ($rawScore === null || trim((string) $rawScore) === '') {
                        $parsedScore = null;
                        $studentAllComponentsFilled = false;
                    } else {
                        $numVal = (float) $rawScore;
                        if ($numVal < 0 || $numVal > 100) {
                            throw ValidationException::withMessages([
                                "scores.{$studentId}.{$component->id}" => 'Nilai harus berada pada rentang 0 sampai 100.',
                            ]);
                        }
                        $parsedScore = round($numVal, 2);
                        $weightedScoreSum += ($parsedScore * ($component->weight / 100.0));
                    }

                    StudentAssessmentScore::query()->updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'subject_assignment_id' => $assignment->id,
                            'assessment_component_id' => $component->id,
                        ],
                        [
                            'score' => $parsedScore,
                        ]
                    );
                }

                // Perhitungan nilai akhir dan status kelengkapan
                $description = isset($descriptionsInput[$studentId]) ? trim((string) $descriptionsInput[$studentId]) : null;

                if ($studentAllComponentsFilled && $activeComponentCount > 0) {
                    $finalScore = round($weightedScoreSum, 2);
                    $isComplete = true;
                    $completedCount++;
                } else {
                    $finalScore = null;
                    $isComplete = false;
                    $incompleteCount++;
                }

                StudentSubjectResult::query()->updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_assignment_id' => $assignment->id,
                    ],
                    [
                        'final_score' => $finalScore,
                        'competency_description' => $description,
                        'is_complete' => $isComplete,
                    ]
                );
            }
        });

        return redirect()->route('admin.assessments.index', [
            'semester_id' => $assignment->semester_id,
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
        ])->with('status', "Penilaian akademik berhasil disimpan. {$completedCount} siswa lengkap, {$incompleteCount} siswa belum lengkap.");
    }
}
