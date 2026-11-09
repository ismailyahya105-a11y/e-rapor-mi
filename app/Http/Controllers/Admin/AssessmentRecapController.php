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
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentRecapController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('school_class_id');
        $selectedSubjectId = $request->query('subject_id');
        $statusFilter = $request->query('status', 'all');
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
        $recapRows = collect();

        // Metrik Ringkasan Nyata (Real Database Statistics)
        $totalStudents = 0;
        $completeCount = 0;
        $incompleteCount = 0;
        $averageScore = 0.0;
        $highestScore = null;
        $lowestScore = null;

        if ($selectedSemester && $selectedClass && $selectedSubject) {
            $assignment = SubjectAssignment::query()
                ->where('semester_id', $selectedSemester->id)
                ->where('school_class_id', $selectedClass->id)
                ->where('subject_id', $selectedSubject->id)
                ->first();

            if ($assignment) {
                $components = AssessmentComponent::query()
                    ->where('subject_id', $selectedSubject->id)
                    ->where('semester_id', $selectedSemester->id)
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->orderBy('id')
                    ->get();

                // Dapatkan seluruh siswa kelas & semester ini
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
                $totalStudents = $students->count();

                // Ambil semua nilai dan hasil untuk assignment ini
                $scores = StudentAssessmentScore::query()
                    ->where('subject_assignment_id', $assignment->id)
                    ->get()
                    ->groupBy('student_id');

                $results = StudentSubjectResult::query()
                    ->where('subject_assignment_id', $assignment->id)
                    ->get()
                    ->keyBy('student_id');

                $completedScores = [];

                foreach ($students as $student) {
                    $res = $results->get($student->id);
                    $isComplete = $res?->is_complete ?? false;
                    $finalScore = $res?->final_score;

                    if ($isComplete) {
                        $completeCount++;
                        if ($finalScore !== null) {
                            $completedScores[] = $finalScore;
                        }
                    } else {
                        $incompleteCount++;
                    }

                    // Terapkan filter status jika bukan 'all'
                    if ($statusFilter === 'complete' && ! $isComplete) {
                        continue;
                    }
                    if ($statusFilter === 'incomplete' && $isComplete) {
                        continue;
                    }

                    $studentScores = $scores->get($student->id, collect())->keyBy('assessment_component_id');

                    $recapRows->push([
                        'student' => $student,
                        'scores' => $studentScores,
                        'final_score' => $finalScore,
                        'description' => $res?->competency_description,
                        'is_complete' => $isComplete,
                    ]);
                }

                if (! empty($completedScores)) {
                    $averageScore = round(array_sum($completedScores) / count($completedScores), 2);
                    $highestScore = max($completedScores);
                    $lowestScore = min($completedScores);
                }
            }
        }

        return view('admin.assessments.recap', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'assignedSubjects' => $assignedSubjects,
            'selectedSubject' => $selectedSubject,
            'assignment' => $assignment,
            'components' => $components,
            'recapRows' => $recapRows,
            'totalStudents' => $totalStudents,
            'completeCount' => $completeCount,
            'incompleteCount' => $incompleteCount,
            'averageScore' => $averageScore,
            'highestScore' => $highestScore,
            'lowestScore' => $lowestScore,
            'statusFilter' => $statusFilter,
            'search' => $search,
        ]);
    }
}
