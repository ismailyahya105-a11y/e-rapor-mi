<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $academicYears = AcademicYear::query()
            ->with(['semesters'])
            ->withCount(['semesters', 'schoolClasses'])
            ->latest()
            ->paginate(10);

        return view('admin.academic-years.index', [
            'academicYears' => $academicYears,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:academic_years,name'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama tahun ajaran wajib diisi.',
            'name.unique' => 'Nama tahun ajaran sudah terdaftar.',
            'name.max' => 'Nama tahun ajaran maksimal 50 karakter.',
        ]);

        $academicYear = AcademicYear::query()->create([
            'name' => $validated['name'],
            'is_active' => false,
        ]);

        // Secara otomatis siapkan semester Ganjil dan Genap untuk tahun ajaran baru
        $ganjil = $academicYear->semesters()->create([
            'name' => 'Ganjil',
            'is_active' => false,
        ]);

        $academicYear->semesters()->create([
            'name' => 'Genap',
            'is_active' => false,
        ]);

        // Jika diminta aktif atau belum ada tahun ajaran aktif sama sekali
        if ($request->boolean('is_active') || ! AcademicYear::query()->where('is_active', true)->exists()) {
            $ganjil->activate();
        }

        return redirect()->route('admin.academic-years.index')
            ->with('status', 'Tahun ajaran berhasil ditambahkan beserta semester Ganjil dan Genap.');
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('academic_years', 'name')->ignore($academicYear->id)],
        ], [
            'name.required' => 'Nama tahun ajaran wajib diisi.',
            'name.unique' => 'Nama tahun ajaran sudah terdaftar.',
            'name.max' => 'Nama tahun ajaran maksimal 50 karakter.',
        ]);

        $academicYear->update($validated);

        return redirect()->route('admin.academic-years.index')
            ->with('status', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $hasClasses = $academicYear->schoolClasses()->exists();
        $hasEnrollments = $academicYear->semesters()->whereHas('enrollments')->exists();

        if ($hasClasses || $hasEnrollments) {
            return redirect()->route('admin.academic-years.index')
                ->with('error', 'Tahun ajaran tidak dapat dihapus karena memiliki riwayat kelas atau penempatan siswa terkait.');
        }

        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')
            ->with('status', 'Tahun ajaran berhasil dihapus.');
    }
}
