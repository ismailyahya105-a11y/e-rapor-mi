<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\ReportDraft;
use App\Models\ReportPublication;
use App\Models\Semester;
use App\Models\Student;
use App\Services\ReportCompletenessChecker;
use App\Services\ReportDraftBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportDraftController extends Controller
{
    public function index(): View
    {
        $semester = Semester::query()->where('is_active', true)->first();
        $enrollments = $semester ? Enrollment::query()->where('semester_id', $semester->id)->with('student')->get() : collect();
        $drafts = ReportDraft::query()->whereIn('enrollment_id', $enrollments->pluck('id'))->get()->keyBy('student_id');

        return view('admin.report-drafts.index', compact('semester', 'enrollments', 'drafts'));
    }

    public function show(Request $request, Student $student, ReportCompletenessChecker $checker): View
    {
        $semester = Semester::query()->findOrFail($request->integer('semester_id'));
        $enrollment = Enrollment::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->with('schoolClass')->firstOrFail();
        $draft = ReportDraft::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->first();
        $check = $checker->check($student, $semester, $draft);
        $publication = $draft ? ReportPublication::query()->where('report_draft_id', $draft->id)->first() : null;

        return view('admin.report-drafts.show', compact('student', 'semester', 'enrollment', 'draft', 'check', 'publication'));
    }

    public function refresh(Request $request, Student $student, ReportDraftBuilder $builder): RedirectResponse
    {
        $semester = Semester::query()->findOrFail($request->integer('semester_id'));
        if ($semester->isLocked()) {
            return back()->with('error', 'Semester ini telah dikunci/diarsipkan. Draf rapor tidak dapat diperbarui.');
        }

        $builder->refresh($student, $semester, $request->user());

        return redirect()->route('admin.report-drafts.preview', ['student' => $student, 'semester_id' => $semester->id]);
    }

    public function preview(Request $request, Student $student, ReportCompletenessChecker $checker): View
    {
        $semester = Semester::query()->findOrFail($request->integer('semester_id'));
        $draft = ReportDraft::query()->where('student_id', $student->id)->where('semester_id', $semester->id)->firstOrFail();
        $check = $checker->check($student, $semester, $draft);

        return view('admin.report-drafts.preview', compact('student', 'semester', 'draft', 'check'));
    }
}
