<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttitudeAspect;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentAttitudeRating;
use App\Models\StudentAttitudeSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttitudeAssessmentController extends Controller
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

        $selectedClass = $selectedClassId
            ? SchoolClass::query()->find($selectedClassId)
            : $availableClasses->first();

        // Ambil aspek penilaian sikap yang aktif
        $aspects = AttitudeAspect::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $students = collect();
        $existingRatings = [];
        $existingSummaries = [];

        if ($selectedSemester && $selectedClass) {
            $enrollmentsQuery = Enrollment::query()
                ->where('semester_id', $selectedSemester->id)
                ->where('school_class_id', $selectedClass->id)
                ->with('student');

            if ($search) {
                $enrollmentsQuery->whereHas('student', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            }

            $enrollments = $enrollmentsQuery->get();
            $students = $enrollments->pluck('student')->sortBy('name');

            // Ambil rating sikap siswa yang sudah tersimpan
            $ratings = StudentAttitudeRating::query()
                ->where('semester_id', $selectedSemester->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();

            foreach ($ratings as $r) {
                $existingRatings[$r->student_id][$r->attitude_aspect_id] = [
                    'rating' => $r->rating,
                    'notes' => $r->development_notes,
                ];
            }

            // Ambil catatan rangkuman sikap
            $summaries = StudentAttitudeSummary::query()
                ->where('semester_id', $selectedSemester->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();

            foreach ($summaries as $s) {
                $existingSummaries[$s->student_id] = $s->general_notes;
            }
        }

        return view('admin.attitudes.index', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'aspects' => $aspects,
            'students' => $students,
            'existingRatings' => $existingRatings,
            'existingSummaries' => $existingSummaries,
            'search' => $search,
            'allAspects' => AttitudeAspect::query()->orderBy('order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'semester_id' => ['required', 'exists:semesters,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'ratings' => ['nullable', 'array'],
            'notes' => ['nullable', 'array'],
            'summaries' => ['nullable', 'array'],
        ]);

        $semesterId = $validated['semester_id'];
        $classId = $validated['school_class_id'];

        $validStudentIds = Enrollment::query()
            ->where('semester_id', $semesterId)
            ->where('school_class_id', $classId)
            ->pluck('student_id')
            ->flip()
            ->toArray();

        $ratingsInput = $request->input('ratings', []);
        $notesInput = $request->input('notes', []);
        $summariesInput = $request->input('summaries', []);
        $activeAspectIds = AttitudeAspect::query()->where('is_active', true)->pluck('id')->toArray();

        DB::transaction(function () use ($semesterId, $validStudentIds, $ratingsInput, $notesInput, $summariesInput, $activeAspectIds) {
            $userId = auth()->id();

            foreach ($ratingsInput as $studentId => $aspectRatings) {
                if (! isset($validStudentIds[$studentId])) {
                    abort(403, 'Akses ditolak: Percobaan memanipulasi penilaian sikap untuk siswa luar kelas.');
                }

                foreach ($aspectRatings as $aspectId => $ratingVal) {
                    if (! in_array($aspectId, $activeAspectIds)) {
                        continue;
                    }

                    $trimmedRating = trim((string) $ratingVal);
                    $devNotes = isset($notesInput[$studentId][$aspectId]) ? trim((string) $notesInput[$studentId][$aspectId]) : null;

                    if ($trimmedRating !== '') {
                        StudentAttitudeRating::query()->updateOrCreate(
                            [
                                'student_id' => $studentId,
                                'semester_id' => $semesterId,
                                'attitude_aspect_id' => $aspectId,
                            ],
                            [
                                'rating' => $trimmedRating,
                                'development_notes' => $devNotes,
                                'assessed_by_user_id' => $userId,
                            ]
                        );
                    }
                }

                // Catatan umum sikap siswa
                $generalNote = isset($summariesInput[$studentId]) ? trim((string) $summariesInput[$studentId]) : null;
                if ($generalNote !== null && $generalNote !== '') {
                    StudentAttitudeSummary::query()->updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'semester_id' => $semesterId,
                        ],
                        [
                            'general_notes' => $generalNote,
                            'assessed_by_user_id' => $userId,
                        ]
                    );
                }
            }
        });

        return redirect()->route('admin.attitudes.index', [
            'semester_id' => $semesterId,
            'school_class_id' => $classId,
        ])->with('status', 'Penilaian sikap dan perilaku siswa berhasil disimpan.');
    }

    public function storeAspect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:attitude_aspects,name'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        AttitudeAspect::query()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', "Aspek sikap {$validated['name']} berhasil ditambahkan.");
    }

    public function updateAspect(Request $request, AttitudeAspect $aspect): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('attitude_aspects', 'name')->ignore($aspect->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $aspect->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'] ?? $aspect->order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', "Aspek sikap {$aspect->name} berhasil diperbarui.");
    }

    public function initializeStandardAspects(): RedirectResponse
    {
        $standards = [
            ['name' => 'Kedisiplinan', 'description' => 'Ketaatan terhadap tata tertib, ketepatan waktu hadir dan mengumpulkan tugas.', 'order' => 1],
            ['name' => 'Tanggung Jawab', 'description' => 'Kesungguhan dalam menjalankan amanah dan menyelesaikan kewajiban belajar.', 'order' => 2],
            ['name' => 'Sopan Santun', 'description' => 'Perilaku hormat kepada guru, orang tua, dan santun bertutur kata sesama teman.', 'order' => 3],
            ['name' => 'Kejujuran', 'description' => 'Kesesuaian antara ucapan dan perbuatan, tidak berbohong atau berbuat curang.', 'order' => 4],
            ['name' => 'Kerja Sama', 'description' => 'Kemampuan bergotong royong dan aktif berkontribusi dalam kegiatan kelompok.', 'order' => 5],
            ['name' => 'Kemandirian', 'description' => 'Prakarsa dan inisiatif pribadi dalam belajar tanpa bergantung berlebih.', 'order' => 6],
            ['name' => 'Kepedulian', 'description' => 'Kepekaan sosial, empati, dan kepedulian terhadap kebersihan lingkungan madrasah.', 'order' => 7],
        ];

        foreach ($standards as $s) {
            AttitudeAspect::query()->firstOrCreate(
                ['name' => $s['name']],
                ['description' => $s['description'], 'order' => $s['order'], 'is_active' => true]
            );
        }

        return back()->with('status', '7 Aspek sikap standar madrasah berhasil diinisialisasi.');
    }
}
