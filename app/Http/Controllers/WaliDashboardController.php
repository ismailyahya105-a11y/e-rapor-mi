<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $activeSemester = Semester::query()
            ->where('is_active', true)
            ->with('academicYear')
            ->first();

        $children = $request->user()->students()
            ->with([
                'enrollments' => function ($query) use ($activeSemester): void {
                    if ($activeSemester) {
                        $query->where('semester_id', $activeSemester->id)->with('schoolClass');
                    }
                },
                'enrollments.schoolClass',
                'enrollments.semester.academicYear',
                'reportPublications' => function ($query): void {
                    $query->with('semester.academicYear')->latest('published_at');
                },
            ])
            ->get();

        return view('wali.dashboard', [
            'children' => $children,
            'activeSemester' => $activeSemester,
        ]);
    }
}
