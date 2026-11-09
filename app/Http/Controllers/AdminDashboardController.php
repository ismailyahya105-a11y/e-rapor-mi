<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        $activeSemester = Semester::query()
            ->where('is_active', true)
            ->with('academicYear')
            ->first();

        $activeAcademicYear = $activeSemester?->academicYear
            ?? AcademicYear::query()->where('is_active', true)->first();

        return view('admin.dashboard', [
            'adminCount' => User::query()->where('role', 'admin')->count(),
            'waliCount' => User::query()->where('role', 'wali')->count(),
            'userCount' => User::query()->count(),
            'studentsCount' => Student::query()->count(),
            'activeStudentsCount' => Student::query()->active()->count(),
            'classesCount' => SchoolClass::query()->count(),
            'activeSubjectsCount' => Subject::query()->active()->count(),
            'activeSemester' => $activeSemester,
            'activeAcademicYear' => $activeAcademicYear,
            'activeEnrollmentCount' => $activeSemester ? Enrollment::query()->where('semester_id', $activeSemester->id)->count() : 0,
            'recentStudents' => Student::query()->latest()->take(5)->get(),
        ]);
    }
}
