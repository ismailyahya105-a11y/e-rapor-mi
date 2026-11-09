<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $status = $request->query('status');
        $gender = $request->query('gender');

        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();

        $students = Student::query()
            ->with(['enrollments' => function ($query) use ($activeSemester): void {
                if ($activeSemester) {
                    $query->where('semester_id', $activeSemester->id)->with('schoolClass');
                }
            }, 'guardians'])
            ->when($search, function ($query, $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status): void {
                $query->where('status', $status);
            })
            ->when($gender, function ($query, $gender): void {
                $query->where('gender', $gender);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'activeSemester' => $activeSemester,
            'search' => $search,
            'selectedStatus' => $status,
            'selectedGender' => $gender,
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:30', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:students,nisn'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', Rule::in(['Laki-laki', 'Perempuan'])],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['aktif', 'lulus', 'mutasi', 'nonaktif'])],
        ], [
            'nis.required' => 'Nomor Induk Siswa (NIS) wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan oleh siswa lain.',
            'nisn.unique' => 'NISN sudah digunakan oleh siswa lain.',
            'name.required' => 'Nama lengkap siswa wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Pilihan jenis kelamin tidak valid.',
            'status.required' => 'Status siswa wajib dipilih.',
        ]);

        $student = Student::query()->create($validated);

        return redirect()->route('admin.students.show', $student)
            ->with('status', "Data siswa {$student->name} berhasil ditambahkan.");
    }

    public function show(Student $student): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();

        $student->load([
            'enrollments.schoolClass.academicYear',
            'enrollments.semester.academicYear',
            'guardians',
        ]);

        return view('admin.students.show', [
            'student' => $student,
            'activeSemester' => $activeSemester,
            'currentEnrollment' => $student->currentEnrollment($activeSemester),
        ]);
    }

    public function edit(Student $student): View
    {
        return view('admin.students.edit', [
            'student' => $student,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:30', Rule::unique('students', 'nis')->ignore($student->id)],
            'nisn' => ['nullable', 'string', 'max:30', Rule::unique('students', 'nisn')->ignore($student->id)],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', Rule::in(['Laki-laki', 'Perempuan'])],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['aktif', 'lulus', 'mutasi', 'nonaktif'])],
        ], [
            'nis.required' => 'Nomor Induk Siswa (NIS) wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan oleh siswa lain.',
            'nisn.unique' => 'NISN sudah digunakan oleh siswa lain.',
            'name.required' => 'Nama lengkap siswa wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Pilihan jenis kelamin tidak valid.',
            'status.required' => 'Status siswa wajib dipilih.',
        ]);

        $student->update($validated);

        return redirect()->route('admin.students.show', $student)
            ->with('status', "Data siswa {$student->name} berhasil diperbarui.");
    }

    public function destroy(Student $student): RedirectResponse
    {
        if ($student->enrollments()->exists() || $student->guardians()->exists()) {
            return back()->with('error', 'Siswa ini memiliki riwayat penempatan kelas atau hubungan wali. Ubah status siswa menjadi nonaktif atau mutasi sebagai gantinya.');
        }

        $studentName = $student->name;
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('status', "Data siswa {$studentName} berhasil dihapus.");
    }
}
