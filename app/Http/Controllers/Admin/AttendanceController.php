<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('school_class_id');
        $selectedDate = $request->query('date', date('Y-m-d'));
        $tab = $request->query('tab', 'daily'); // 'daily' or 'recap'

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
        $dailyRecords = [];
        $semesterRecap = [];
        $totalRecordedDays = 0;

        if ($selectedSemester && $selectedClass) {
            $enrollments = Enrollment::query()
                ->where('semester_id', $selectedSemester->id)
                ->where('school_class_id', $selectedClass->id)
                ->with('student')
                ->get();

            $students = $enrollments->pluck('student')->sortBy('name');

            // Ambil presensi harian untuk tanggal terpilih
            $attendancesToday = StudentAttendance::query()
                ->where('semester_id', $selectedSemester->id)
                ->where('school_class_id', $selectedClass->id)
                ->whereDate('date', $selectedDate)
                ->get();

            foreach ($attendancesToday as $att) {
                $dailyRecords[$att->student_id] = [
                    'status' => $att->status,
                    'notes' => $att->notes,
                ];
            }

            // Hitung rekap semester sebenarnya dari database
            $allClassAttendances = StudentAttendance::query()
                ->where('semester_id', $selectedSemester->id)
                ->where('school_class_id', $selectedClass->id)
                ->get();

            $totalRecordedDays = $allClassAttendances->pluck('date')->unique()->count();

            $grouped = $allClassAttendances->groupBy('student_id');

            foreach ($students as $student) {
                $studentRecords = $grouped->get($student->id, collect());
                $hasData = $studentRecords->isNotEmpty();

                $semesterRecap[$student->id] = [
                    'has_data' => $hasData,
                    'hadir' => $studentRecords->where('status', 'Hadir')->count(),
                    'sakit' => $studentRecords->where('status', 'Sakit')->count(),
                    'izin' => $studentRecords->where('status', 'Izin')->count(),
                    'alpa' => $studentRecords->where('status', 'Alpa')->count(),
                    'total' => $studentRecords->count(),
                ];
            }
        }

        return view('admin.attendances.index', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'selectedDate' => $selectedDate,
            'tab' => $tab,
            'students' => $students,
            'dailyRecords' => $dailyRecords,
            'semesterRecap' => $semesterRecap,
            'totalRecordedDays' => $totalRecordedDays,
        ]);
    }

    public function storeDaily(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'semester_id' => ['required', 'exists:semesters,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'date' => ['required', 'date'],
            'attendances' => ['required', 'array'],
            'notes' => ['nullable', 'array'],
        ]);

        $semesterId = $validated['semester_id'];
        $classId = $validated['school_class_id'];
        $date = $validated['date'];

        $validStudentIds = Enrollment::query()
            ->where('semester_id', $semesterId)
            ->where('school_class_id', $classId)
            ->pluck('student_id')
            ->flip()
            ->toArray();

        $attendancesInput = $request->input('attendances', []);
        $notesInput = $request->input('notes', []);
        $userId = auth()->id();

        DB::transaction(function () use ($semesterId, $classId, $date, $validStudentIds, $attendancesInput, $notesInput, $userId) {
            foreach ($attendancesInput as $studentId => $status) {
                if (! isset($validStudentIds[$studentId])) {
                    abort(403, 'Akses ditolak: Siswa tidak terdaftar di kelas dan semester ini.');
                }

                $trimmedStatus = trim((string) $status);
                if (! in_array($trimmedStatus, ['Hadir', 'Sakit', 'Izin', 'Alpa'])) {
                    continue;
                }

                $note = isset($notesInput[$studentId]) ? trim((string) $notesInput[$studentId]) : null;

                // Cegah kehadiran ganda dengan updateOrCreate pada unique [student_id, date, semester_id]
                StudentAttendance::query()->updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'date' => $date,
                        'semester_id' => $semesterId,
                    ],
                    [
                        'school_class_id' => $classId,
                        'status' => $trimmedStatus,
                        'notes' => $note,
                        'recorded_by_user_id' => $userId,
                    ]
                );
            }
        });

        return redirect()->route('admin.attendances.index', [
            'semester_id' => $semesterId,
            'school_class_id' => $classId,
            'date' => $date,
            'tab' => 'daily',
        ])->with('status', "Presensi kehadiran tanggal {$date} berhasil disimpan untuk kelas terpilih.");
    }
}
