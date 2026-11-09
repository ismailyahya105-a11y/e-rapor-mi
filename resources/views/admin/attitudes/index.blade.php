@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Penilaian Sikap dan Perilaku')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Penilaian Nonakademik</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Penilaian Sikap dan Perilaku</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Catat perkembangan karakter dan adab santri/siswa madrasah berdasarkan pengamatan nyata para guru.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <details class="relative">
                    <summary class="cursor-pointer list-none inline-flex items-center gap-2 rounded-2xl border border-emerald-950/20 bg-white px-4 py-2.5 text-xs font-bold text-emerald-900 shadow-sm hover:bg-emerald-50 transition">
                        <span>⚙️</span>
                        <span>Kelola Aspek Sikap ({{ $allAspects->count() }})</span>
                    </summary>
                    <div class="absolute right-0 top-12 z-30 w-80 sm:w-96 rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-2xl space-y-4">
                        <div class="flex items-center justify-between pb-2 border-b border-emerald-100">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-950">Konfigurasi Aspek Sikap</h3>
                            <form method="POST" action="{{ route('admin.attitudes.aspects.standard') }}">
                                @csrf
                                <button type="submit" class="text-[10px] font-bold text-emerald-700 hover:text-emerald-900 underline">
                                    + Reset 7 Standar MI
                                </button>
                            </form>
                        </div>

                        <!-- Form Tambah Aspek Baru -->
                        <form method="POST" action="{{ route('admin.attitudes.aspects.store') }}" class="space-y-2.5">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-emerald-900/70">Nama Aspek Baru</label>
                                <input type="text" name="name" placeholder="Misal: Kepedulian Lingkungan" required class="mt-1 w-full rounded-xl border border-emerald-200 px-3 py-1.5 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-emerald-900/70">Deskripsi / Indikator</label>
                                <textarea name="description" rows="2" placeholder="Indikator perilaku yang diamati..." class="mt-1 w-full rounded-xl border border-emerald-200 p-2 text-xs"></textarea>
                            </div>
                            <button type="submit" class="w-full rounded-xl bg-emerald-800 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-900">
                                + Tambah Aspek
                            </button>
                        </form>

                        <div class="max-h-48 overflow-y-auto divide-y divide-emerald-100 pt-2 text-xs">
                            @foreach ($allAspects as $asp)
                                <div class="py-2 flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-emerald-950">{{ $asp->name }}</div>
                                        <div class="text-[10px] text-emerald-900/60 line-clamp-1">{{ $asp->description }}</div>
                                    </div>
                                    <span class="rounded-full px-2 py-0.5 text-[9px] font-bold {{ $asp->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $asp->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </details>

                <a href="{{ route('admin.non-academic-recap.index', ['semester_id' => $selectedSemester?->id, 'school_class_id' => $selectedClass?->id]) }}" class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-emerald-950 shadow-md shadow-amber-950/10 hover:bg-amber-300 transition">
                    <span>📋</span>
                    <span>Buka Rekap Nonakademik →</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.attitudes.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                            <option value="">Belum ada kelas pada semester ini</option>
                        @endforelse
                    </select>
                </div>

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

        @if (!$selectedSemester || !$selectedClass)
            <div class="rounded-3xl border border-dashed border-emerald-300 bg-emerald-50/50 p-12 text-center">
                <span class="text-4xl">⭐</span>
                <h3 class="mt-3 text-base font-bold text-emerald-950">Pilih Semester dan Kelas</h3>
                <p class="text-xs text-emerald-900/60 mt-1">Pilih semester dan kelas untuk mengisi penilaian sikap santri/siswa.</p>
            </div>
        @elseif ($aspects->isEmpty())
            <div class="rounded-3xl border border-amber-300 bg-amber-50/80 p-8 text-center text-amber-950">
                <span class="text-3xl">⚙️</span>
                <h3 class="mt-2 text-base font-bold">Belum Ada Aspek Sikap yang Aktif</h3>
                <p class="mt-1 text-xs text-amber-900/80">Silakan inisialisasi 7 aspek sikap standar madrasah untuk memulai penilaian.</p>
                <form method="POST" action="{{ route('admin.attitudes.aspects.standard') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-800 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-900">
                        ⚡ Inisialisasi 7 Aspek Standar MI Sekarang
                    </button>
                </form>
            </div>
        @elseif ($students->isEmpty())
            <div class="rounded-3xl border border-emerald-950/10 bg-white p-12 text-center text-emerald-900/50">
                Tidak ada siswa yang terdaftar di Kelas {{ $selectedClass->name }} untuk semester ini.
            </div>
        @else
            <!-- Form Input Sikap Massal -->
            <form method="POST" action="{{ route('admin.attitudes.store') }}">
                @csrf
                <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                <input type="hidden" name="school_class_id" value="{{ $selectedClass->id }}">

                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-emerald-100">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Lembar Penilaian Sikap</span>
                            <h2 class="text-xl font-black text-emerald-950">
                                Kelas {{ $selectedClass->name }} — {{ $selectedSemester->academicYear?->name }} (Semester {{ $selectedSemester->term }})
                            </h2>
                            <p class="text-xs text-emerald-900/60 mt-0.5">
                                {{ $students->count() }} Siswa • {{ $aspects->count() }} Aspek Sikap yang Diamati
                            </p>
                        </div>
                    </div>

                    <!-- Tabel Sikap Massal -->
                    <div class="overflow-x-auto rounded-2xl border border-emerald-100">
                        <table class="w-full text-left text-sm text-emerald-950">
                            <thead class="border-b border-emerald-100 bg-emerald-50/70 text-xs uppercase font-bold text-emerald-900/80">
                                <tr>
                                    <th class="px-3 py-3 w-10 text-center">No</th>
                                    <th class="px-4 py-3 min-w-[180px]">Siswa</th>
                                    @foreach ($aspects as $asp)
                                        <th class="px-3 py-3 min-w-[140px] text-center" title="{{ $asp->description }}">
                                            <div>{{ $asp->name }}</div>
                                        </th>
                                    @endforeach
                                    <th class="px-4 py-3 min-w-[220px]">Catatan Perkembangan Sikap Umum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100/60">
                                @foreach ($students as $idx => $student)
                                    <tr class="hover:bg-emerald-50/30 transition">
                                        <td class="px-3 py-3 text-center text-xs font-semibold text-emerald-900/60">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-emerald-950 text-xs sm:text-sm">{{ $student->name }}</div>
                                            <div class="text-[11px] font-mono text-emerald-900/60">NIS: {{ $student->nis }}</div>
                                        </td>

                                        @foreach ($aspects as $asp)
                                            @php
                                                $curr = $existingRatings[$student->id][$asp->id] ?? null;
                                                $currRating = $curr['rating'] ?? '';
                                                $currNote = $curr['notes'] ?? '';
                                            @endphp
                                            <td class="px-3 py-2 text-center">
                                                <div class="space-y-1">
                                                    <select name="ratings[{{ $student->id }}][{{ $asp->id }}]"
                                                            class="w-full rounded-xl border border-emerald-200 px-2 py-1.5 text-xs font-bold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                                        <option value="">-- Pilih --</option>
                                                        <option value="Sangat Baik" {{ $currRating === 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                                        <option value="Baik" {{ $currRating === 'Baik' ? 'selected' : '' }}>Baik</option>
                                                        <option value="Cukup" {{ $currRating === 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                                        <option value="Perlu Bimbingan" {{ $currRating === 'Perlu Bimbingan' ? 'selected' : '' }}>Perlu Bimbingan</option>
                                                    </select>

                                                    <input type="text"
                                                           name="notes[{{ $student->id }}][{{ $asp->id }}]"
                                                           value="{{ $currNote }}"
                                                           placeholder="Catatan..."
                                                           class="w-full rounded-lg border border-emerald-100 px-2 py-1 text-[10px] text-emerald-900 focus:outline-none">
                                                </div>
                                            </td>
                                        @endforeach

                                        <td class="px-4 py-2">
                                            <textarea name="summaries[{{ $student->id }}]"
                                                      rows="2"
                                                      placeholder="Catatan umum perkembangan karakter..."
                                                      class="w-full rounded-xl border border-emerald-200 p-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">{{ $existingSummaries[$student->id] ?? '' }}</textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Action Bar -->
                    <div class="sticky bottom-4 z-20 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-emerald-950/20 bg-emerald-950 p-4 text-white shadow-2xl">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 place-items-center rounded-xl bg-amber-400 text-emerald-950 font-black">⭐</span>
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-amber-400">Penyimpanan Terpadu</div>
                                <div class="text-xs text-emerald-100/80">Penilaian sikap disimpan berdasarkan semester untuk menjamin keutuhan data historis.</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit" class="rounded-xl bg-amber-400 px-6 py-2.5 text-xs font-black text-emerald-950 shadow-md shadow-amber-950/20 hover:bg-amber-300 transition">
                                Simpan Penilaian Sikap ({{ $students->count() }} Siswa)
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection

