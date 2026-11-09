<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $status = $request->query('status');

        $subjects = Subject::query()
            ->withCount('subjectAssignments')
            ->when($search, function ($query, $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status): void {
                $query->where('is_active', (bool) $status);
            })
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.subjects.index', [
            'subjects' => $subjects,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.required' => 'Kode mata pelajaran wajib diisi.',
            'code.unique' => 'Kode mata pelajaran sudah digunakan.',
            'name.required' => 'Nama mata pelajaran wajib diisi.',
        ]);

        $validated['order'] = $validated['order'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        Subject::query()->create($validated);

        return redirect()->route('admin.subjects.index')
            ->with('status', "Mata pelajaran {$validated['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'name' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.required' => 'Kode mata pelajaran wajib diisi.',
            'code.unique' => 'Kode mata pelajaran sudah digunakan.',
            'name.required' => 'Nama mata pelajaran wajib diisi.',
        ]);

        $validated['order'] = $validated['order'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')
            ->with('status', "Mata pelajaran {$subject->name} berhasil diperbarui.");
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->subjectAssignments()->exists()) {
            // Karena memiliki riwayat, lakukan penonaktifan alih-alih merusak integritas relasi
            $subject->update(['is_active' => false]);

            return redirect()->route('admin.subjects.index')
                ->with('status', 'Mata pelajaran memiliki riwayat penugasan, sehingga statusnya dinonaktifkan alih-alih dihapus permanen.');
        }

        $subjectName = $subject->name;
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('status', "Mata pelajaran {$subjectName} berhasil dihapus.");
    }
}
