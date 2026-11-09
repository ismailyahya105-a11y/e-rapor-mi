<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentComponent;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssessmentComponentController extends Controller
{
    public function index(Request $request): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedSubjectId = $request->query('subject_id');

        $semesters = Semester::query()->with('academicYear')->orderBy('id', 'desc')->get();
        $selectedSemester = $selectedSemesterId
            ? Semester::query()->with('academicYear')->find($selectedSemesterId)
            : $activeSemester;

        $subjects = Subject::query()->where('is_active', true)->orderBy('order')->orderBy('name')->get();
        $selectedSubject = $selectedSubjectId
            ? Subject::query()->find($selectedSubjectId)
            : $subjects->first();

        $components = collect();
        $totalWeight = 0;

        if ($selectedSemester && $selectedSubject) {
            $components = AssessmentComponent::query()
                ->where('subject_id', $selectedSubject->id)
                ->where('semester_id', $selectedSemester->id)
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            $totalWeight = (float) $components->where('is_active', true)->sum('weight');
        }

        // Hitung ringkasan status bobot untuk seluruh mapel pada semester terpilih
        $subjectsSummary = [];
        if ($selectedSemester) {
            foreach ($subjects as $subj) {
                $weightSum = (float) AssessmentComponent::query()
                    ->where('subject_id', $subj->id)
                    ->where('semester_id', $selectedSemester->id)
                    ->where('is_active', true)
                    ->sum('weight');

                $count = AssessmentComponent::query()
                    ->where('subject_id', $subj->id)
                    ->where('semester_id', $selectedSemester->id)
                    ->count();

                $subjectsSummary[$subj->id] = [
                    'weight' => $weightSum,
                    'count' => $count,
                    'is_ready' => abs($weightSum - 100.0) < 0.001,
                ];
            }
        }

        return view('admin.assessments.components', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'components' => $components,
            'totalWeight' => $totalWeight,
            'subjectsSummary' => $subjectsSummary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('assessment_components')->where(function ($query) use ($request) {
                    return $query->where('subject_id', $request->input('subject_id'))
                        ->where('semester_id', $request->input('semester_id'));
                }),
            ],
            'weight' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama komponen penilaian wajib diisi.',
            'name.unique' => 'Nama komponen sudah terdaftar untuk mata pelajaran dan semester ini.',
            'weight.required' => 'Bobot persentase wajib diisi.',
            'weight.min' => 'Bobot tidak boleh kurang dari atau sama dengan 0%.',
            'weight.max' => 'Bobot tidak boleh melebihi 100%.',
        ]);

        $subjectId = $validated['subject_id'];
        $semesterId = $validated['semester_id'];
        $weight = (float) $validated['weight'];
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;

        if ($isActive) {
            $currentTotal = (float) AssessmentComponent::query()
                ->where('subject_id', $subjectId)
                ->where('semester_id', $semesterId)
                ->where('is_active', true)
                ->sum('weight');

            if (($currentTotal + $weight) > 100.001) {
                $available = max(0, 100 - $currentTotal);
                throw ValidationException::withMessages([
                    'weight' => "Total bobot akan melebihi 100%. Saat ini total bobot aktif adalah {$currentTotal}%. Maksimal yang dapat ditambahkan adalah {$available}%.",
                ]);
            }
        }

        AssessmentComponent::query()->create([
            'subject_id' => $subjectId,
            'semester_id' => $semesterId,
            'name' => $validated['name'],
            'weight' => $weight,
            'order' => $validated['order'] ?? 0,
            'is_active' => $isActive,
        ]);

        return redirect()->route('admin.assessments.components.index', [
            'semester_id' => $semesterId,
            'subject_id' => $subjectId,
        ])->with('status', "Komponen penilaian {$validated['name']} ({$weight}%) berhasil ditambahkan.");
    }

    public function update(Request $request, AssessmentComponent $component): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('assessment_components')
                    ->where('subject_id', $component->subject_id)
                    ->where('semester_id', $component->semester_id)
                    ->ignore($component->id),
            ],
            'weight' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama komponen penilaian wajib diisi.',
            'name.unique' => 'Nama komponen sudah terdaftar untuk mata pelajaran ini.',
            'weight.required' => 'Bobot persentase wajib diisi.',
            'weight.min' => 'Bobot tidak boleh kurang dari atau sama dengan 0%.',
            'weight.max' => 'Bobot tidak boleh melebihi 100%.',
        ]);

        $weight = (float) $validated['weight'];
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;

        if ($isActive) {
            $otherTotal = (float) AssessmentComponent::query()
                ->where('subject_id', $component->subject_id)
                ->where('semester_id', $component->semester_id)
                ->where('id', '!=', $component->id)
                ->where('is_active', true)
                ->sum('weight');

            if (($otherTotal + $weight) > 100.001) {
                $available = max(0, 100 - $otherTotal);
                throw ValidationException::withMessages([
                    'weight' => "Total bobot akan melebihi 100%. Komponen lain berjumlah {$otherTotal}%. Bobot maksimal untuk komponen ini adalah {$available}%.",
                ]);
            }
        }

        $component->update([
            'name' => $validated['name'],
            'weight' => $weight,
            'order' => $validated['order'] ?? $component->order,
            'is_active' => $isActive,
        ]);

        return redirect()->route('admin.assessments.components.index', [
            'semester_id' => $component->semester_id,
            'subject_id' => $component->subject_id,
        ])->with('status', "Komponen penilaian {$component->name} berhasil diperbarui.");
    }

    public function destroy(AssessmentComponent $component): RedirectResponse
    {
        $subjectId = $component->subject_id;
        $semesterId = $component->semester_id;
        $name = $component->name;

        // Jika sudah memiliki nilai siswa, nonaktifkan untuk melindungi data historis
        if ($component->scores()->exists()) {
            $component->update(['is_active' => false]);

            return redirect()->route('admin.assessments.components.index', [
                'semester_id' => $semesterId,
                'subject_id' => $subjectId,
            ])->with('status', "Komponen {$name} memiliki riwayat nilai siswa terkait, sehingga statusnya dinonaktifkan alih-alih dihapus permanen demi keutuhan data.");
        }

        $component->delete();

        return redirect()->route('admin.assessments.components.index', [
            'semester_id' => $semesterId,
            'subject_id' => $subjectId,
        ])->with('status', "Komponen penilaian {$name} berhasil dihapus.");
    }

    public function applyPreset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'preset_type' => ['required', 'in:standard_3,standard_4'],
        ]);

        $subjectId = $validated['subject_id'];
        $semesterId = $validated['semester_id'];

        // Preset 1: Tugas (40%), Penilaian Tengah Semester (30%), Penilaian Akhir Semester (30%)
        // Preset 2: Tugas/PR (20%), Ulangan Harian (30%), Penilaian Tengah Semester (25%), Penilaian Akhir Semester (25%)
        $presets = [
            'standard_3' => [
                ['name' => 'Tugas & Praktik', 'weight' => 40.00, 'order' => 1],
                ['name' => 'Penilaian Tengah Semester (PTS)', 'weight' => 30.00, 'order' => 2],
                ['name' => 'Penilaian Akhir Semester (PAS)', 'weight' => 30.00, 'order' => 3],
            ],
            'standard_4' => [
                ['name' => 'Tugas Harian', 'weight' => 20.00, 'order' => 1],
                ['name' => 'Ulangan Harian', 'weight' => 30.00, 'order' => 2],
                ['name' => 'Penilaian Tengah Semester (PTS)', 'weight' => 25.00, 'order' => 3],
                ['name' => 'Penilaian Akhir Semester (PAS)', 'weight' => 25.00, 'order' => 4],
            ],
        ];

        $selectedPreset = $presets[$validated['preset_type']];

        // Hapus komponen yang belum memiliki nilai siswa, atau nonaktifkan
        $existing = AssessmentComponent::query()
            ->where('subject_id', $subjectId)
            ->where('semester_id', $semesterId)
            ->get();

        foreach ($existing as $ex) {
            if ($ex->scores()->exists()) {
                $ex->update(['is_active' => false]);
            } else {
                $ex->delete();
            }
        }

        foreach ($selectedPreset as $p) {
            AssessmentComponent::query()->create([
                'subject_id' => $subjectId,
                'semester_id' => $semesterId,
                'name' => $p['name'],
                'weight' => $p['weight'],
                'order' => $p['order'],
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.assessments.components.index', [
            'semester_id' => $semesterId,
            'subject_id' => $subjectId,
        ])->with('status', 'Preset komponen penilaian standar (Total 100%) berhasil diterapkan.');
    }
}
