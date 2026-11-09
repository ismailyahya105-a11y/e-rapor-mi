<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SemesterController extends Controller
{
    public function store(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                Rule::in(['Ganjil', 'Genap']),
                Rule::unique('semesters')->where('academic_year_id', $academicYear->id),
            ],
        ], [
            'name.required' => 'Pilihan semester wajib diisi.',
            'name.in' => 'Nama semester harus Ganjil atau Genap.',
            'name.unique' => 'Semester tersebut sudah ada pada tahun ajaran ini.',
        ]);

        $academicYear->semesters()->create([
            'name' => $validated['name'],
            'is_active' => false,
        ]);

        return redirect()->route('admin.academic-years.index')
            ->with('status', "Semester {$validated['name']} berhasil ditambahkan.");
    }

    public function activate(Semester $semester): RedirectResponse
    {
        $semester->activate();

        return back()->with('status', "Semester {$semester->full_name} kini aktif sebagai periode penilaian saat ini.");
    }

    public function lock(Request $request, Semester $semester): RedirectResponse
    {
        $semester->lock($request->user());

        return back()->with('status', "Semester {$semester->full_name} berhasil dikunci dan diarsipkan. Penilaian dan draf tidak dapat diubah.");
    }

    public function unlock(Semester $semester): RedirectResponse
    {
        $semester->unlock();

        return back()->with('status', "Kunci semester {$semester->full_name} berhasil dibuka. Penilaian dan draf dapat diperbarui kembali.");
    }
}
