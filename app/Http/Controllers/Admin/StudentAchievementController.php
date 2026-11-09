<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentAchievement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAchievementController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('school_class_id');
        $levelFilter = $request->query('level');
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

        $achievementsQuery = StudentAchievement::query()
            ->with(['student', 'semester']);

        if ($selectedSemester) {
            $achievementsQuery->where('semester_id', $selectedSemester->id);
        }

        if ($selectedClass && $students->isNotEmpty()) {
            $achievementsQuery->whereIn('student_id', $students->pluck('id'));
        }

        if ($levelFilter) {
            $achievementsQuery->where('level', $levelFilter);
        }

        if ($search) {
            $achievementsQuery->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                })->orWhere('achievement_name', 'like', "%{$search}%")
                    ->orWhere('organizer', 'like', "%{$search}%");
            });
        }

        $achievements = $achievementsQuery->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();

        $levels = ['Madrasah', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional'];
        $activityTypes = ['Keagamaan', 'Akademik', 'Seni & Budaya', 'Olahraga', 'Lainnya'];

        return view('admin.achievements.index', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'students' => $students,
            'achievements' => $achievements,
            'levelFilter' => $levelFilter,
            'search' => $search,
            'levels' => $levels,
            'activityTypes' => $activityTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'achievement_name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', 'max:50'],
            'activity_type' => ['required', 'string', 'max:50'],
            'rank' => ['required', 'string', 'max:100'],
            'organizer' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ], [
            'student_id.required' => 'Pilih siswa yang berprestasi.',
            'achievement_name.required' => 'Nama kejuaraan atau prestasi wajib diisi.',
            'rank.required' => 'Peringkat atau predikat penghargaan wajib diisi.',
            'organizer.required' => 'Penyelenggara kegiatan wajib diisi.',
        ]);

        StudentAchievement::query()->create($validated);

        return back()->with('status', 'Catatan prestasi siswa berhasil disimpan.');
    }

    public function destroy(StudentAchievement $achievement): RedirectResponse
    {
        $achievement->delete();

        return back()->with('status', 'Catatan prestasi berhasil dihapus.');
    }
}
