<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuardianController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');

        $guardians = User::query()
            ->where('role', 'wali')
            ->with(['students' => function ($query): void {
                $query->orderBy('name');
            }])
            ->withCount('students')
            ->when($search, function ($query, $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $allStudents = Student::query()
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();

        return view('admin.guardians.index', [
            'guardians' => $guardians,
            'search' => $search,
            'allStudents' => $allStudents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'student_id' => ['nullable', 'exists:students,id'],
            'relationship' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'Nama lengkap wali wajib diisi.',
            'email.required' => 'Alamat email wali wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar sebagai akun lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $guardian = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => 'wali',
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        if (! empty($validated['student_id'])) {
            $relationship = $validated['relationship'] ?: 'Wali';
            GuardianStudent::query()->create([
                'user_id' => $guardian->id,
                'student_id' => $validated['student_id'],
                'relationship' => $relationship,
            ]);
        }

        return redirect()->route('admin.guardians.index')
            ->with('status', "Akun wali {$guardian->name} berhasil dibuat.");
    }

    public function linkStudent(Request $request, User $guardian): RedirectResponse
    {
        if (! $guardian->isWali()) {
            abort(400, 'Hanya akun dengan peran wali yang dapat dihubungkan ke siswa.');
        }

        $validated = $request->validate([
            'student_id' => [
                'required',
                'exists:students,id',
                Rule::unique('guardian_students', 'student_id')->where('user_id', $guardian->id),
            ],
            'relationship' => ['nullable', 'string', 'max:50'],
        ], [
            'student_id.required' => 'Siswa wajib dipilih.',
            'student_id.unique' => 'Siswa ini sudah terhubung dengan akun wali tersebut.',
        ]);

        $relationship = $validated['relationship'] ?: 'Wali';

        GuardianStudent::query()->create([
            'user_id' => $guardian->id,
            'student_id' => $validated['student_id'],
            'relationship' => $relationship,
        ]);

        return redirect()->route('admin.guardians.index')
            ->with('status', 'Hubungan anak dan wali berhasil ditambahkan.');
    }

    public function unlinkStudent(User $guardian, Student $student): RedirectResponse
    {
        GuardianStudent::query()
            ->where('user_id', $guardian->id)
            ->where('student_id', $student->id)
            ->delete();

        return redirect()->route('admin.guardians.index')
            ->with('status', 'Hubungan anak berhasil diputuskan dari akun wali.');
    }
}
