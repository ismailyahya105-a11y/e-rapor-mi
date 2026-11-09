@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Penilaian Akademik Siswa')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Asesmen & Pembelajaran</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Input Penilaian Akademik</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Masukkan nilai komponen siswa, simpan secara massal, dan sesuaikan deskripsi capaian pembelajaran.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('admin.assessments.components.index', ['semester_id' => $selectedSemester?->id, 'subject_id' => $selectedSubject?->id]) }}" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-950/20 bg-white px-4 py-2.5 text-xs font-bold text-emerald-900 hover:bg-emerald-50 transition">
                    <span>⚙️</span>
                    <span>Pengaturan Komponen</span>
                </a>
                <a href="{{ route('admin.assessments.recap', ['semester_id' => $selectedSemester?->id, 'school_class_id' => $selectedClass?->id, 'subject_id' => $selectedSubject?->id]) }}" class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-emerald-950 shadow-md shadow-amber-950/10 hover:bg-amber-300 transition">
                    <span>📊</span>
                    <span>Rekap Nilai →</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar Pemilihan Kelas & Mapel -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.assessments.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Semester -->
                <div>
                    <label for="filter_semester" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Semester & Tahun Ajaran
                    </label>
                    <select id="filter_semester"
                            name="semester_id"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $selectedSemester && $selectedSemester->id === $sem->id ? 'selected' : '' }}>
                                {{ $sem->academicYear?->name ?? 'Tahun ?' }} — Sem. {{ $sem->term }} {{ $sem->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelas -->
                <div>
                    <label for="filter_class" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Kelas
                    </label>
                    <select id="filter_class"
                            name="school_class_id"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        @forelse ($availableClasses as $c)
                            <option value="{{ $c->id }}" {{ $selectedClass && $selectedClass->id === $c->id ? 'selected' : '' }}>
                                Kelas {{ $c->name }} (Tingkat {{ $c->grade_level }})
                            </option>
                        @empty
                            <option value="">Belum ada kelas untuk semester ini</option>
                        @endforelse
                    </select>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label for="filter_subject" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Mata Pelajaran Ditugaskan
                    </label>
                    <select id="filter_subject"
                            name="subject_id"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        @forelse ($assignedSubjects as $subj)
                            <option value="{{ $subj->id }}" {{ $selectedSubject && $selectedSubject->id === $subj->id ? 'selected' : '' }}>
                                [{{ $subj->code }}] {{ $subj->name }}
                            </option>
                        @empty
                            <option value="">Belum ada mapel ditugaskan ke kelas ini</option>
                        @endforelse
                    </select>
                </div>

                <!-- Cari Siswa -->
                <div>
                    <label for="filter_search" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Cari Siswa
                    </label>
                    <div class="mt-1.5 flex gap-2">
                        <input type="text"
                               id="filter_search"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Nama atau NIS..."
                               class="w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <button type="submit" class="rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-900 transition">
                            Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if (!$selectedSemester || !$selectedClass || !$selectedSubject)
            <div class="rounded-3xl border border-dashed border-emerald-300 bg-emerald-50/50 p-12 text-center">
                <span class="text-4xl">🔍</span>
                <h3 class="mt-3 text-base font-bold text-emerald-950">Pilih Semester, Kelas, dan Mata Pelajaran</h3>
                <p class="text-xs text-emerald-900/60 mt-1">Gunakan formulir filter di atas untuk memulai penginputan nilai siswa.</p>
            </div>
        @elseif (!$assignment)
            <div class="rounded-3xl border border-amber-300 bg-amber-50/80 p-8 text-center text-amber-950">
                <span class="text-3xl">⚠️</span>
                <h3 class="mt-2 text-base font-bold">Mata Pelajaran Belum Ditugaskan</h3>
                <p class="mt-1 text-xs text-amber-900/80">
                    Mata pelajaran <strong>{{ $selectedSubject->name }}</strong> belum ditugaskan ke <strong>Kelas {{ $selectedClass->name }}</strong> pada semester ini.
                </p>
                <div class="mt-4">
                    <a href="{{ route('admin.subject-assignments.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-800 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-900">
                        Tugaskan Mapel ke Kelas Ini →
                    </a>
                </div>
            </div>
        @elseif ($components->isEmpty())
            <div class="rounded-3xl border border-rose-300 bg-rose-50/80 p-8 text-center text-rose-950">
                <span class="text-3xl">⚙️</span>
                <h3 class="mt-2 text-base font-bold">Komponen Penilaian Belum Diatur</h3>
                <p class="mt-1 text-xs text-rose-900/80">
                    Mata pelajaran <strong>{{ $selectedSubject->name }}</strong> belum memiliki komponen penilaian aktif pada semester ini. Nilai belum dapat diinput.
                </p>
                <div class="mt-4">
                    <a href="{{ route('admin.assessments.components.index', ['semester_id' => $selectedSemester->id, 'subject_id' => $selectedSubject->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-800 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-900">
                        Atur Komponen Penilaian Sekarang →
                    </a>
                </div>
            </div>
        @elseif (!$isWeightReady)
            <div class="rounded-3xl border border-amber-300 bg-amber-50/90 p-5 text-amber-950 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <h4 class="text-sm font-bold">Total Bobot Komponen Belum 100% (Saat Ini: {{ number_format($totalWeight, 0) }}%)</h4>
                        <p class="text-xs text-amber-900/80 mt-0.5">
                            Perhitungan nilai akhir hanya akan akurat jika total bobot komponen bernilai tepat 100%.
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.assessments.components.index', ['semester_id' => $selectedSemester->id, 'subject_id' => $selectedSubject->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2 text-xs font-bold text-emerald-950 hover:bg-amber-300 self-start sm:self-auto shrink-0">
                    Sesuaikan Bobot Jadi 100% →
                </a>
            </div>
        @endif

        @if ($assignment && $components->isNotEmpty())
            <!-- Form Input Nilai Massal -->
            <form method="POST" action="{{ route('admin.assessments.store') }}" id="assessment-form">
                @csrf
                <input type="hidden" name="subject_assignment_id" value="{{ $assignment->id }}">

                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-6">
                    <!-- Bar Informasi Pembelajaran -->
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-emerald-100">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Lembar Penilaian</span>
                            <h2 class="text-xl font-black text-emerald-950">
                                [{{ $selectedSubject->code }}] {{ $selectedSubject->name }} — Kelas {{ $selectedClass->name }}
                            </h2>
                            <p class="text-xs text-emerald-900/60 mt-0.5">
                                {{ $selectedSemester->academicYear?->name }} • Semester {{ $selectedSemester->term }} • {{ $students->count() }} Siswa Terdaftar
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                {{ $components->count() }} Komponen Penilaian (Total {{ number_format($totalWeight, 0) }}%)
                            </span>
                        </div>
                    </div>

                    @if ($students->isEmpty())
                        <div class="py-12 text-center text-sm text-emerald-900/50">
                            Tidak ada siswa yang terdaftar di Kelas {{ $selectedClass->name }} untuk semester ini.
                        </div>
                    @else
                        <!-- Tabel Input Nilai -->
                        <div class="overflow-x-auto rounded-2xl border border-emerald-100">
                            <table class="w-full text-left text-sm text-emerald-950" id="assessment-table">
                                <thead class="border-b border-emerald-100 bg-emerald-50/70 text-xs uppercase font-bold text-emerald-900/80">
                                    <tr>
                                        <th class="px-3 py-3 w-10 text-center">No</th>
                                        <th class="px-4 py-3 min-w-[180px]">Siswa</th>
                                        @foreach ($components as $comp)
                                            <th class="px-3 py-3 min-w-[110px] text-center" title="Bobot: {{ number_format($comp->weight, 0) }}%">
                                                <div>{{ $comp->name }}</div>
                                                <div class="text-[10px] font-mono text-emerald-700/80 font-normal">({{ number_format($comp->weight, 0) }}%)</div>
                                            </th>
                                        @endforeach
                                        <th class="px-3 py-3 w-28 text-center bg-amber-50/60">
                                            <div>Nilai Akhir</div>
                                            <div class="text-[10px] font-normal text-amber-900/70">Otomatis</div>
                                        </th>
                                        <th class="px-3 py-3 min-w-[240px]">Deskripsi Capaian Pembelajaran</th>
                                        <th class="px-3 py-3 w-28 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-emerald-100/60">
                                    @foreach ($students as $index => $student)
                                        @php
                                            $res = $existingResults[$student->id] ?? null;
                                            $isComplete = $res?->is_complete ?? false;
                                            $finalScore = $res?->final_score;
                                        @endphp
                                        <tr class="hover:bg-emerald-50/30 transition student-row" data-student-id="{{ $student->id }}">
                                            <td class="px-3 py-3 text-center text-xs font-semibold text-emerald-900/60">
                                                {{ $index + 1 }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="font-bold text-emerald-950 text-xs sm:text-sm">{{ $student->name }}</div>
                                                <div class="text-[11px] font-mono text-emerald-900/60">NIS: {{ $student->nis }}</div>
                                            </td>

                                            @foreach ($components as $comp)
                                                @php
                                                    $currScore = $existingScores[$student->id][$comp->id] ?? null;
                                                @endphp
                                                <td class="px-3 py-2 text-center">
                                                    <input type="number"
                                                           name="scores[{{ $student->id }}][{{ $comp->id }}]"
                                                           value="{{ $currScore !== null ? number_format($currScore, 2, '.', '') : '' }}"
                                                           min="0"
                                                           max="100"
                                                           step="0.01"
                                                           placeholder="-"
                                                           data-student-id="{{ $student->id }}"
                                                           data-weight="{{ $comp->weight }}"
                                                           class="score-input w-20 text-center rounded-xl border border-emerald-200 px-2 py-1.5 text-xs font-mono font-bold focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                                </td>
                                            @endforeach

                                            <!-- Nilai Akhir Otomatis -->
                                            <td class="px-3 py-2 text-center bg-amber-50/40">
                                                <span class="final-score-display text-xs font-mono font-black text-emerald-950">
                                                    {{ $finalScore !== null ? number_format($finalScore, 2) : '-' }}
                                                </span>
                                            </td>

                                            <!-- Deskripsi Capaian Pembelajaran -->
                                            <td class="px-3 py-2">
                                                <div class="space-y-1">
                                                    <textarea name="descriptions[{{ $student->id }}]"
                                                              rows="2"
                                                              placeholder="Tulis capaian atau gunakan tombol saran..."
                                                              class="desc-input w-full rounded-xl border border-emerald-200 p-2 text-xs font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">{{ $res?->competency_description }}</textarea>

                                                    <button type="button"
                                                            onclick="applyDraftSuggestion('{{ $student->id }}', '{{ addslashes($student->name) }}', '{{ addslashes($selectedSubject->name) }}')"
                                                            class="text-[10px] font-semibold text-emerald-800 hover:text-emerald-950 hover:underline">
                                                        💡 Terapkan Saran Deskripsi
                                                    </button>
                                                </div>
                                            </td>

                                            <!-- Status Kelengkapan -->
                                            <td class="px-3 py-2 text-center">
                                                <span class="status-badge inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $isComplete ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                    {{ $isComplete ? 'Lengkap' : 'Belum Lengkap' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Sticky Action Bar Bawah -->
                        <div class="sticky bottom-4 z-20 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-emerald-950/20 bg-emerald-950 p-4 text-white shadow-2xl">
                            <div class="flex items-center gap-3">
                                <span class="grid size-9 place-items-center rounded-xl bg-amber-400 text-emerald-950 font-black">
                                    💾
                                </span>
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wider text-amber-400">Penyimpanan Terpadu</div>
                                    <div class="text-xs text-emerald-100/80">
                                        Perhitungan nilai akhir dihitung secara konsisten di server dengan pembulatan 2 desimal.
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="reset" class="rounded-xl border border-emerald-700 px-4 py-2 text-xs font-semibold text-emerald-200 hover:bg-emerald-900 transition">
                                    Batal Perubahan
                                </button>
                                <button type="submit" class="rounded-xl bg-amber-400 px-6 py-2.5 text-xs font-black text-emerald-950 shadow-md shadow-amber-950/20 hover:bg-amber-300 transition">
                                    Simpan Seluruh Nilai ({{ $students->count() }} Siswa)
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </form>
        @endif
    </div>

    <!-- Client-side Script untuk Live Preview Kalkulasi & Saran Deskripsi -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const table = document.getElementById('assessment-table');
            if (!table) return;

            const rows = table.querySelectorAll('.student-row');

            function updateRowCalculation(row) {
                const inputs = row.querySelectorAll('.score-input');
                let allFilled = true;
                let weightedSum = 0;
                let activeCount = inputs.length;

                inputs.forEach(input => {
                    const valStr = input.value.trim();
                    const weight = parseFloat(input.dataset.weight || '0');

                    if (valStr === '' || isNaN(parseFloat(valStr))) {
                        allFilled = false;
                    } else {
                        const scoreVal = parseFloat(valStr);
                        weightedSum += (scoreVal * (weight / 100));
                    }
                });

                const display = row.querySelector('.final-score-display');
                const badge = row.querySelector('.status-badge');

                if (allFilled && activeCount > 0) {
                    const finalScore = (Math.round(weightedSum * 100) / 100).toFixed(2);
                    display.textContent = finalScore;
                    badge.textContent = 'Lengkap';
                    badge.className = 'status-badge inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-emerald-100 text-emerald-800';
                } else {
                    display.textContent = '-';
                    badge.textContent = 'Belum Lengkap';
                    badge.className = 'status-badge inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-amber-100 text-amber-800';
                }
            }

            rows.forEach(row => {
                const inputs = row.querySelectorAll('.score-input');
                inputs.forEach(input => {
                    input.addEventListener('input', () => updateRowCalculation(row));
                });
            });
        });

        function applyDraftSuggestion(studentId, studentName, subjectName) {
            const row = document.querySelector(`.student-row[data-student-id="${studentId}"]`);
            if (!row) return;

            const display = row.querySelector('.final-score-display');
            const textarea = row.querySelector('.desc-input');
            const scoreVal = parseFloat(display.textContent);

            let suggestion = '';
            if (!isNaN(scoreVal)) {
                if (scoreVal >= 85) {
                    suggestion = `Menunjukkan penguasaan yang sangat baik dalam seluruh kompetensi dan materi pembelajaran ${subjectName}.`;
                } else if (scoreVal >= 75) {
                    suggestion = `Menunjukkan pemahaman yang baik dalam mencapai capaian pembelajaran ${subjectName}.`;
                } else if (scoreVal >= 60) {
                    suggestion = `Cukup memahami materi pokok ${subjectName}, perlu bimbingan mandiri untuk meningkatkan pemahaman lanjutan.`;
                } else {
                    suggestion = `Perlu bimbingan dan pendampingan intensif dalam memahami materi dasar ${subjectName}.`;
                }
            } else {
                suggestion = `Menunjukkan antusiasme yang baik dalam mengikuti pembelajaran ${subjectName}.`;
            }

            textarea.value = suggestion;
        }
    </script>
@endsection

