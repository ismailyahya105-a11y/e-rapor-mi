<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Extracurricular;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentExtracurricular;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExtracurricularController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('school_class_id');
        $selectedExtraId = $request->query('extracurricular_id');

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

        $extracurriculars = Extracurricular::query()->orderBy('name')->get();
        $activeExtracurriculars = $extracurriculars->where('is_active', true);

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

        // Ambil data keikutsertaan ekskul siswa pada semester terpilih
        $participationsQuery = StudentExtracurricular::query()
            ->with(['student', 'extracurricular', 'semester'])
            ->where('semester_id', $selectedSemester?->id);

        if ($selectedClass && $students->isNotEmpty()) {
            $participationsQuery->whereIn('student_id', $students->pluck('id'));
        }

        if ($selectedExtraId) {
            $participationsQuery->where('extracurricular_id', $selectedExtraId);
        }

        $participations = $participationsQuery->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('admin.extracurriculars.index', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'students' => $students,
            'extracurriculars' => $extracurriculars,
            'activeExtracurriculars' => $activeExtracurriculars,
            'participations' => $participations,
            'selectedExtraId' => $selectedExtraId,
        ]);
    }

    public function storeMaster(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:extracurriculars,name'],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Extracurricular::query()->create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', "Kegiatan ekstrakurikuler {$validated['name']} berhasil ditambahkan.");
    }

    public function updateMaster(Request $request, Extracurricular $extracurricular): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('extracurriculars', 'name')->ignore($extracurricular->id)],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $extracurricular->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', "Kegiatan ekstrakurikuler {$extracurricular->name} berhasil diperbarui.");
    }

    public function storeParticipation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'extracurricular_id' => ['required', 'exists:extracurriculars,id'],
            'predicate' => ['required', 'in:Sangat Baik,Baik,Cukup'],
            'description' => ['nullable', 'string'],
        ], [
            'student_id.required' => 'Pilih siswa.',
            'extracurricular_id.required' => 'Pilih kegiatan ekstrakurikuler.',
            'predicate.required' => 'Pilih predikat capaian kegiatan.',
        ]);

        StudentExtracurricular::query()->updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'semester_id' => $validated['semester_id'],
                'extracurricular_id' => $validated['extracurricular_id'],
            ],
            [
                'predicate' => $validated['predicate'],
                'description' => $validated['description'] ?? null,
            ]
        );

        return back()->with('status', 'Keikutsertaan ekstrakurikuler siswa berhasil disimpan.');
    }

    public function destroyParticipation(StudentExtracurricular $studentExtracurricular): RedirectResponse
    {
        $studentExtracurricular->delete();

        return back()->with('status', 'Keikutsertaan ekstrakurikuler siswa berhasil dihapus.');
    }
}
