<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $academicYearId = $request->query('academic_year_id');
        $gradeLevel = $request->query('grade_level');

        $activeYear = AcademicYear::query()->where('is_active', true)->first();

        $classes = SchoolClass::query()
            ->with(['academicYear'])
            ->withCount(['enrollments', 'subjectAssignments'])
            ->when($search, function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($academicYearId, function ($query, $academicYearId): void {
                $query->where('academic_year_id', $academicYearId);
            }, function ($query) use ($activeYear): void {
                if ($activeYear) {
                    $query->where('academic_year_id', $activeYear->id);
                }
            })
            ->when($gradeLevel, function ($query, $gradeLevel): void {
                $query->where('grade_level', $gradeLevel);
            })
            ->orderBy('grade_level')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $academicYears = AcademicYear::query()->orderBy('name', 'desc')->get();

        return view('admin.school-classes.index', [
            'classes' => $classes,
            'academicYears' => $academicYears,
            'selectedYearId' => $academicYearId ?? $activeYear?->id,
            'selectedGrade' => $gradeLevel,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade_level' => ['required', 'integer', 'between:1,6'],
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('school_classes')->where('academic_year_id', $request->input('academic_year_id')),
            ],
        ], [
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak valid.',
            'grade_level.required' => 'Tingkat kelas wajib dipilih.',
            'grade_level.between' => 'Tingkat kelas harus antara 1 sampai 6.',
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas sudah digunakan pada tahun ajaran ini.',
        ]);

        SchoolClass::query()->create($validated);

        return redirect()->route('admin.school-classes.index', ['academic_year_id' => $validated['academic_year_id']])
            ->with('status', "Kelas {$validated['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade_level' => ['required', 'integer', 'between:1,6'],
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('school_classes')
                    ->where('academic_year_id', $request->input('academic_year_id'))
                    ->ignore($schoolClass->id),
            ],
        ], [
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak valid.',
            'grade_level.required' => 'Tingkat kelas wajib dipilih.',
            'grade_level.between' => 'Tingkat kelas harus antara 1 sampai 6.',
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas sudah digunakan pada tahun ajaran ini.',
        ]);

        $schoolClass->update($validated);

        return redirect()->route('admin.school-classes.index', ['academic_year_id' => $validated['academic_year_id']])
            ->with('status', "Data kelas {$schoolClass->name} berhasil diperbarui.");
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        if ($schoolClass->enrollments()->exists()) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena sudah memiliki riwayat penempatan siswa.');
        }

        if ($schoolClass->subjectAssignments()->exists()) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena memiliki riwayat penugasan mata pelajaran.');
        }

        $className = $schoolClass->name;
        $academicYearId = $schoolClass->academic_year_id;
        $schoolClass->delete();

        return redirect()->route('admin.school-classes.index', ['academic_year_id' => $academicYearId])
            ->with('status', "Kelas {$className} berhasil dihapus.");
    }
}
