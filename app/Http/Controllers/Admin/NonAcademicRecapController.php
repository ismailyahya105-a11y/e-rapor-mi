<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\HomeroomNote;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentActivity;
use App\Models\StudentAttendance;
use App\Models\StudentAttitudeRating;
use App\Models\StudentAttitudeSummary;
use App\Models\StudentExtracurricular;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NonAcademicRecapController extends Controller
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
        $recapList = collect();

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
            $studentIds = $students->pluck('id');

            // Ambil semua data nonakademik siswa
            $attendancesGrouped = StudentAttendance::query()
                ->where('semester_id', $selectedSemester->id)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->groupBy('student_id');

            $attitudeRatingsGrouped = StudentAttitudeRating::query()
                ->where('semester_id', $selectedSemester->id)
                ->whereIn('student_id', $studentIds)
                ->with('aspect')
                ->get()
                ->groupBy('student_id');

            $activitiesCount = StudentActivity::query()
                ->where('semester_id', $selectedSemester->id)
                ->whereIn('student_id', $studentIds)
                ->selectRaw('student_id, count(*) as total')
                ->groupBy('student_id')
                ->pluck('total', 'student_id');

            $extracurricularsGrouped = StudentExtracurricular::query()
                ->where('semester_id', $selectedSemester->id)
                ->whereIn('student_id', $studentIds)
                ->with('extracurricular')
                ->get()
                ->groupBy('student_id');

            $achievementsCount = StudentAchievement::query()
                ->where('semester_id', $selectedSemester->id)
                ->whereIn('student_id', $studentIds)
                ->selectRaw('student_id, count(*) as total')
                ->groupBy('student_id')
                ->pluck('total', 'student_id');

            $homeroomNotes = HomeroomNote::query()
                ->where('semester_id', $selectedSemester->id)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->keyBy('student_id');

            foreach ($students as $student) {
                $studentAtt = $attendancesGrouped->get($student->id, collect());
                $studentRatings = $attitudeRatingsGrouped->get($student->id, collect());
                $studentExtras = $extracurricularsGrouped->get($student->id, collect());
                $hasNote = $homeroomNotes->has($student->id);

                $recapList->push([
                    'student' => $student,
                    'attendance' => [
                        'has_data' => $studentAtt->isNotEmpty(),
                        'hadir' => $studentAtt->where('status', 'Hadir')->count(),
                        'sakit' => $studentAtt->where('status', 'Sakit')->count(),
                        'izin' => $studentAtt->where('status', 'Izin')->count(),
                        'alpa' => $studentAtt->where('status', 'Alpa')->count(),
                    ],
                    'attitude_count' => $studentRatings->count(),
                    'attitude_sample' => $studentRatings->take(3),
                    'activities_count' => $activitiesCount->get($student->id, 0),
                    'extras' => $studentExtras,
                    'achievements_count' => $achievementsCount->get($student->id, 0),
                    'has_homeroom_note' => $hasNote,
                    'homeroom_note' => $homeroomNotes->get($student->id),
                ]);
            }
        }

        return view('admin.non-academic-recap.index', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'students' => $students,
            'recapList' => $recapList,
            'search' => $search,
        ]);
    }

    public function show(Request $request, Student $student): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);

        $semesters = Semester::query()->with('academicYear')->orderBy('id', 'desc')->get();
        $selectedSemester = $selectedSemesterId
            ? Semester::query()->with('academicYear')->find($selectedSemesterId)
            : $activeSemester;

        $enrollment = Enrollment::query()
            ->where('student_id', $student->id)
            ->where('semester_id', $selectedSemester?->id)
            ->with('schoolClass')
            ->first();

        // 1. Kehadiran
        $attendances = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->where('semester_id', $selectedSemester?->id)
            ->orderBy('date')
            ->get();

        $attendanceStats = [
            'has_data' => $attendances->isNotEmpty(),
            'hadir' => $attendances->where('status', 'Hadir')->count(),
            'sakit' => $attendances->where('status', 'Sakit')->count(),
            'izin' => $attendances->where('status', 'Izin')->count(),
            'alpa' => $attendances->where('status', 'Alpa')->count(),
            'total' => $attendances->count(),
        ];

        // 2. Sikap
        $attitudeRatings = StudentAttitudeRating::query()
            ->where('student_id', $student->id)
            ->where('semester_id', $selectedSemester?->id)
            ->with(['aspect', 'assessor'])
            ->get();

        $attitudeSummary = StudentAttitudeSummary::query()
            ->where('student_id', $student->id)
            ->where('semester_id', $selectedSemester?->id)
            ->first();

        // 3. Keaktifan Belajar
        $activities = StudentActivity::query()
            ->where('student_id', $student->id)
            ->where('semester_id', $selectedSemester?->id)
            ->with('recorder')
            ->orderByDesc('date')
            ->get();

        // 4. Ekstrakurikuler
        $extracurriculars = StudentExtracurricular::query()
            ->where('student_id', $student->id)
            ->where('semester_id', $selectedSemester?->id)
            ->with('extracurricular')
            ->get();

        // 5. Prestasi
        $achievements = StudentAchievement::query()
            ->where('student_id', $student->id)
            ->where('semester_id', $selectedSemester?->id)
            ->orderByDesc('date')
            ->get();

        // 6. Catatan Wali Kelas
        $homeroomNote = HomeroomNote::query()
            ->where('student_id', $student->id)
            ->where('semester_id', $selectedSemester?->id)
            ->with('author')
            ->first();

        return view('admin.non-academic-recap.show', [
            'student' => $student,
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'enrollment' => $enrollment,
            'attendances' => $attendances,
            'attendanceStats' => $attendanceStats,
            'attitudeRatings' => $attitudeRatings,
            'attitudeSummary' => $attitudeSummary,
            'activities' => $activities,
            'extracurriculars' => $extracurriculars,
            'achievements' => $achievements,
            'homeroomNote' => $homeroomNote,
        ]);
    }
}
