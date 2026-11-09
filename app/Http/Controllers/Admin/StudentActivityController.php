<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentActivityController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('school_class_id');
        $aspectFilter = $request->query('aspect');
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
        if ($selectedSemester && $selectedClass) {
            $students = Enrollment::query()
                ->where('semester_id', $selectedSemester->id)
                ->where('school_class_id', $selectedClass->id)
                ->with('student')
                ->get()
                ->pluck('student')
                ->sortBy('name');
        }

        $activitiesQuery = StudentActivity::query()
            ->with(['student', 'semester', 'recorder']);

        if ($selectedSemester) {
            $activitiesQuery->where('semester_id', $selectedSemester->id);
        }

        if ($selectedClass && $students->isNotEmpty()) {
            $activitiesQuery->whereIn('student_id', $students->pluck('id'));
        }

        if ($aspectFilter) {
            $activitiesQuery->where('aspect', $aspectFilter);
        }

        if ($search) {
            $activitiesQuery->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                })->orWhere('description', 'like', "%{$search}%");
            });
        }

        $activities = $activitiesQuery->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();

        $standardAspects = [
            'Keaktifan Bertanya',
            'Partisipasi Diskusi',
            'Keterlibatan Kegiatan Kelas',
            'Kerja Sama Kelompok',
            'Inisiatif Belajar',
        ];

        return view('admin.activities.index', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'students' => $students,
            'activities' => $activities,
            'aspectFilter' => $aspectFilter,
            'search' => $search,
            'standardAspects' => $standardAspects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'date' => ['required', 'date'],
            'aspect' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'min:5'],
            'teacher_notes' => ['nullable', 'string'],
        ], [
            'student_id.required' => 'Pilih siswa yang diamati.',
            'aspect.required' => 'Pilih aspek keaktifan.',
            'description.required' => 'Deskripsi pengamatan konkret wajib diisi.',
        ]);

        StudentActivity::query()->create([
            'student_id' => $validated['student_id'],
            'semester_id' => $validated['semester_id'],
            'date' => $validated['date'],
            'aspect' => $validated['aspect'],
            'description' => $validated['description'],
            'teacher_notes' => $validated['teacher_notes'] ?? null,
            'recorded_by_user_id' => auth()->id(),
        ]);

        return back()->with('status', 'Catatan keaktifan siswa berhasil disimpan.');
    }

    public function destroy(StudentActivity $activity): RedirectResponse
    {
        $activity->delete();

        return back()->with('status', 'Catatan keaktifan berhasil dihapus.');
    }
}
