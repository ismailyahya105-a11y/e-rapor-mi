<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('school_class_id');
        $search = $request->query('q');

        $semesters = Semester::query()->with('academicYear')->orderBy('id', 'desc')->get();

        $selectedSemester = $selectedSemesterId
            ? Semester::query()->with('academicYear')->find($selectedSemesterId)
            : $activeSemester;

        $availableClasses = $selectedSemester
            ? SchoolClass::query()->where('academic_year_id', $selectedSemester->academic_year_id)->orderBy('grade_level')->orderBy('name')->get()
            : collect();

        $enrollments = Enrollment::query()
            ->with(['student', 'schoolClass.academicYear', 'semester.academicYear'])
            ->when($selectedSemesterId, function ($query, $semesterId): void {
                $query->where('semester_id', $semesterId);
            })
            ->when($selectedClassId, function ($query, $classId): void {
                $query->where('school_class_id', $classId);
            })
            ->when($search, function ($query, $search): void {
                $query->whereHas('student', function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->paginate(15)
            ->withQueryString();

        // Ambil siswa aktif yang belum ditempatkan pada semester yang dipilih
        $unplacedStudents = collect();
        if ($selectedSemester) {
            $placedStudentIds = Enrollment::query()
                ->where('semester_id', $selectedSemester->id)
                ->pluck('student_id');

            $unplacedStudents = Student::query()
                ->where('status', 'aktif')
                ->whereNotIn('id', $placedStudentIds)
                ->orderBy('name')
                ->get();
        }

        return view('admin.enrollments.index', [
            'enrollments' => $enrollments,
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClassId' => $selectedClassId,
            'search' => $search,
            'unplacedStudents' => $unplacedStudents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'semester_id' => ['required', 'exists:semesters,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'student_id' => ['required', 'exists:students,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'semester_id.required' => 'Semester wajib dipilih.',
            'school_class_id.required' => 'Kelas wajib dipilih.',
            'student_id.required' => 'Siswa wajib dipilih.',
        ]);

        $semester = Semester::query()->with('academicYear')->findOrFail($validated['semester_id']);
        $schoolClass = SchoolClass::query()->with('academicYear')->findOrFail($validated['school_class_id']);

        // Integritas: Pastikan tahun ajaran kelas dan semester sesuai
        if ($schoolClass->academic_year_id !== $semester->academic_year_id) {
            throw ValidationException::withMessages([
                'school_class_id' => "Tahun ajaran kelas ({$schoolClass->academicYear->name}) tidak sesuai dengan tahun ajaran semester ({$semester->academicYear->name}).",
            ]);
        }

        // Integritas: Satu siswa hanya mempunyai satu penempatan kelas pada semester yang sama
        $existingEnrollment = Enrollment::query()
            ->where('student_id', $validated['student_id'])
            ->where('semester_id', $semester->id)
            ->first();

        if ($existingEnrollment) {
            throw ValidationException::withMessages([
                'student_id' => 'Siswa ini sudah ditempatkan pada kelas lain di semester yang sama. Gunakan menu ubah kelas jika ingin memindahkan.',
            ]);
        }

        Enrollment::query()->create($validated);

        return redirect()->route('admin.enrollments.index', [
            'semester_id' => $semester->id,
            'school_class_id' => $schoolClass->id,
        ])->with('status', 'Siswa berhasil ditempatkan ke kelas.');
    }

    public function update(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'school_class_id.required' => 'Kelas tujuan wajib dipilih.',
        ]);

        $targetClass = SchoolClass::query()->with('academicYear')->findOrFail($validated['school_class_id']);
        $semester = $enrollment->semester()->with('academicYear')->first();

        if ($targetClass->academic_year_id !== $semester->academic_year_id) {
            throw ValidationException::withMessages([
                'school_class_id' => "Tahun ajaran kelas tujuan ({$targetClass->academicYear->name}) tidak sesuai dengan tahun ajaran semester ({$semester->academicYear->name}).",
            ]);
        }

        $enrollment->update([
            'school_class_id' => $targetClass->id,
            'notes' => $validated['notes'] ?? $enrollment->notes,
        ]);

        return redirect()->route('admin.enrollments.index', [
            'semester_id' => $semester->id,
            'school_class_id' => $targetClass->id,
        ])->with('status', 'Penempatan kelas siswa berhasil diperbarui.');
    }

    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        $semesterId = $enrollment->semester_id;
        $classId = $enrollment->school_class_id;

        $enrollment->delete();

        return redirect()->route('admin.enrollments.index', [
            'semester_id' => $semesterId,
            'school_class_id' => $classId,
        ])->with('status', 'Data penempatan siswa berhasil dihapus.');
    }
}
