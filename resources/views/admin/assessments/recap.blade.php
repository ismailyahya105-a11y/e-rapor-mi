@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Rekap Penilaian Akademik')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Laporan & Rekapitulasi</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Rekap Penilaian Akademik</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Pantau statistik capaian, kelengkapan nilai, dan distribusi hasil belajar siswa per kelas dan mata pelajaran.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-950/20 bg-white px-4 py-2.5 text-xs font-bold text-emerald-900 hover:bg-emerald-50 transition print:hidden">
                    <span>🖨️</span>
                    <span>Cetak Lembar Rekap</span>
                </button>
                @if ($selectedSemester && $selectedClass && $selectedSubject)
                    <a href="{{ route('admin.assessments.index', ['semester_id' => $selectedSemester->id, 'school_class_id' => $selectedClass->id, 'subject_id' => $selectedSubject->id]) }}" class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-emerald-950 shadow-md shadow-amber-950/10 hover:bg-amber-300 transition print:hidden">
                        <span>✏️</span>
                        <span>Input / Edit Nilai Ini →</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Filter Bar Multi-level -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm print:hidden">
            <form method="GET" action="{{ route('admin.assessments.recap') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
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
                            <option value="">Belum ada kelas</option>
                        @endforelse
                    </select>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label for="filter_subject" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Mata Pelajaran
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
                            <option value="">Belum ada mapel ditugaskan</option>
                        @endforelse
                    </select>
                </div>

                <!-- Filter Status Kelengkapan -->
                <div>
                    <label for="filter_status" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Status Kelengkapan
                    </label>
                    <select id="filter_status"
                            name="status"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Siswa</option>
                        <option value="complete" {{ $statusFilter === 'complete' ? 'selected' : '' }}>Nilai Lengkap</option>
                        <option value="incomplete" {{ $statusFilter === 'incomplete' ? 'selected' : '' }}>Belum Lengkap</option>
                    </select>
                </div>

                <!-- Pencarian Siswa -->
                <div>
                    <label for="filter_search" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Cari Siswa
                    </label>
                    <div class="mt-1.5 flex gap-2">
                        <input type="text"
                               id="filter_search"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Nama/NIS..."
                               class="w-full rounded-xl border border-emerald-200 px-3 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <button type="submit" class="rounded-xl bg-emerald-800 px-3.5 py-2.5 text-sm font-bold text-white hover:bg-emerald-900 transition">
                            Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if (!$assignment)
            <div class="rounded-3xl border border-dashed border-emerald-300 bg-emerald-50/50 p-12 text-center">
                <span class="text-4xl">📊</span>
                <h3 class="mt-3 text-base font-bold text-emerald-950">Data Penugasan Tidak Ditemukan</h3>
                <p class="text-xs text-emerald-900/60 mt-1">Pilih semester, kelas, dan mata pelajaran yang sudah ditugaskan untuk melihat rekapitulasi penilaian.</p>
            </div>
        @else
            <!-- Ringkasan Statistik Database Riil (6 Metrik Utama) -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <!-- Total Siswa -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Total Siswa</span>
                    <div class="mt-2 text-2xl font-black text-emerald-950 sm:text-3xl">{{ $totalStudents }}</div>
                    <span class="text-[11px] text-emerald-900/60">Terdaftar di kelas</span>
                </div>

                <!-- Siswa Lengkap -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Nilai Lengkap</span>
                    <div class="mt-2 text-2xl font-black text-emerald-700 sm:text-3xl">{{ $completeCount }}</div>
                    <span class="text-[11px] text-emerald-900/60">
                        {{ $totalStudents > 0 ? round(($completeCount / $totalStudents) * 100, 1) : 0 }}% tuntas
                    </span>
                </div>

                <!-- Belum Lengkap -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Belum Lengkap</span>
                    <div class="mt-2 text-2xl font-black text-amber-700 sm:text-3xl">{{ $incompleteCount }}</div>
                    <span class="text-[11px] text-emerald-900/60">Menunggu penilaian</span>
                </div>

                <!-- Rata-rata Kelas -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Rata-rata</span>
                    <div class="mt-2 text-2xl font-black text-emerald-950 sm:text-3xl font-mono">
                        {{ $averageScore > 0 ? number_format($averageScore, 2) : '-' }}
                    </div>
                    <span class="text-[11px] text-emerald-900/60">Dari siswa tuntas</span>
                </div>

                <!-- Nilai Tertinggi -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Tertinggi</span>
                    <div class="mt-2 text-2xl font-black text-emerald-800 sm:text-3xl font-mono">
                        {{ $highestScore !== null ? number_format($highestScore, 2) : '-' }}
                    </div>
                    <span class="text-[11px] text-emerald-900/60">Nilai maksimal</span>
                </div>

                <!-- Nilai Terendah -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Terendah</span>
                    <div class="mt-2 text-2xl font-black text-rose-700 sm:text-3xl font-mono">
                        {{ $lowestScore !== null ? number_format($lowestScore, 2) : '-' }}
                    </div>
                    <span class="text-[11px] text-emerald-900/60">Nilai minimal</span>
                </div>
            </div>

            <!-- Tabel Rekapitulasi Rinci -->
            <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-emerald-100">
                    <div>
                        <h3 class="text-lg font-bold text-emerald-950">
                            Lembar Rekapitulasi: [{{ $selectedSubject->code }}] {{ $selectedSubject->name }}
                        </h3>
                        <p class="text-xs text-emerald-900/60 mt-0.5">
                            Kelas {{ $selectedClass->name }} • {{ $selectedSemester->academicYear?->name }} (Semester {{ $selectedSemester->term }})
                        </p>
                    </div>

                    <div class="text-xs font-semibold text-emerald-900/70">
                        Menampilkan {{ $recapRows->count() }} dari {{ $totalStudents }} siswa
                    </div>
                </div>

                @if ($recapRows->isEmpty())
                    <div class="py-12 text-center text-sm text-emerald-900/50">
                        Tidak ada data siswa yang memenuhi kriteria filter saat ini.
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-emerald-100">
                        <table class="w-full text-left text-sm text-emerald-950">
                            <thead class="border-b border-emerald-100 bg-emerald-50/70 text-xs uppercase font-bold text-emerald-900/80">
                                <tr>
                                    <th class="px-3 py-3 w-10 text-center">No</th>
                                    <th class="px-4 py-3 min-w-[160px]">Siswa</th>
                                    @foreach ($components as $comp)
                                        <th class="px-3 py-3 text-center min-w-[90px]">
                                            <div>{{ $comp->name }}</div>
                                            <div class="text-[10px] font-mono text-emerald-700 font-normal">({{ number_format($comp->weight, 0) }}%)</div>
                                        </th>
                                    @endforeach
                                    <th class="px-3 py-3 text-center w-28 bg-amber-50/60">
                                        <div>Nilai Akhir</div>
                                    </th>
                                    <th class="px-3 py-3 text-center w-24">Predikat</th>
                                    <th class="px-3 py-3 min-w-[220px]">Deskripsi Capaian Pembelajaran</th>
                                    <th class="px-3 py-3 text-center w-28">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100/60">
                                @foreach ($recapRows as $idx => $row)
                                    @php
                                        $student = $row['student'];
                                        $scores = $row['scores'];
                                        $final = $row['final_score'];
                                        $isComplete = $row['is_complete'];

                                        // Hitung Predikat
                                        $predicate = '-';
                                        $predicateBadge = 'bg-gray-100 text-gray-600';
                                        if ($final !== null) {
                                            if ($final >= 85) {
                                                $predicate = 'A (Sangat Baik)';
                                                $predicateBadge = 'bg-emerald-100 text-emerald-800';
                                            } elseif ($final >= 75) {
                                                $predicate = 'B (Baik)';
                                                $predicateBadge = 'bg-blue-100 text-blue-800';
                                            } elseif ($final >= 60) {
                                                $predicate = 'C (Cukup)';
                                                $predicateBadge = 'bg-amber-100 text-amber-800';
                                            } else {
                                                $predicate = 'D (Kurang)';
                                                $predicateBadge = 'bg-rose-100 text-rose-800';
                                            }
                                        }
                                    @endphp
                                    <tr class="hover:bg-emerald-50/30 transition">
                                        <td class="px-3 py-3 text-center text-xs font-semibold text-emerald-900/60">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-emerald-950 text-xs sm:text-sm">{{ $student->name }}</div>
                                            <div class="text-[11px] font-mono text-emerald-900/60">NIS: {{ $student->nis }}</div>
                                        </td>

                                        @foreach ($components as $comp)
                                            @php
                                                $sc = $scores->get($comp->id)?->score;
                                            @endphp
                                            <td class="px-3 py-3 text-center font-mono text-xs">
                                                @if ($sc !== null)
                                                    <span class="font-bold text-emerald-950">{{ number_format($sc, 2) }}</span>
                                                @else
                                                    <span class="text-emerald-900/40">-</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- Nilai Akhir -->
                                        <td class="px-3 py-3 text-center bg-amber-50/40 font-mono font-black text-emerald-950 text-sm">
                                            {{ $final !== null ? number_format($final, 2) : '-' }}
                                        </td>

                                        <!-- Predikat -->
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold {{ $predicateBadge }}">
                                                {{ $predicate }}
                                            </span>
                                        </td>

                                        <!-- Deskripsi Capaian -->
                                        <td class="px-3 py-3 text-xs text-emerald-950/80 leading-relaxed">
                                            {{ $row['description'] ?? 'Belum ada deskripsi capaian pembelajaran.' }}
                                        </td>

                                        <!-- Status Kelengkapan -->
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $isComplete ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $isComplete ? 'Lengkap' : 'Belum Lengkap' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection

