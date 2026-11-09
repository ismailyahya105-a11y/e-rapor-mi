@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Prestasi Siswa')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Penilaian Nonakademik</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Prestasi & Penghargaan Siswa</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Dokumentasikan capaian kejuaraan, penghargaan keagamaan, seni, sains, dan olahraga santri/siswa.</p>
            </div>

            <a href="{{ route('admin.non-academic-recap.index', ['semester_id' => $selectedSemester?->id, 'school_class_id' => $selectedClass?->id]) }}" class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-emerald-950 shadow-md shadow-amber-950/10 hover:bg-amber-300 transition self-start sm:self-auto">
                <span>📋</span>
                <span>Buka Rekap Nonakademik →</span>
            </a>
        </div>

        <!-- Filter Bar -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.achievements.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                            <option value="">Belum ada kelas</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label for="filter_level" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Tingkat Kejuaraan
                    </label>
                    <select id="filter_level"
                            name="level"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <option value="">Semua Tingkat</option>
                        @foreach ($levels as $lvl)
                            <option value="{{ $lvl }}" {{ $levelFilter === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter_search" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Cari Prestasi
                    </label>
                    <div class="mt-1.5 flex gap-2">
                        <input type="text"
                               id="filter_search"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Nama siswa, lomba, penyelenggara..."
                               class="w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <button type="submit" class="rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-900 transition">
                            Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Form Input Prestasi Baru -->
            <div class="lg:col-span-1">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm sticky top-24">
                    <h2 class="text-lg font-bold text-emerald-950">Catat Prestasi Siswa</h2>
                    <p class="text-xs text-emerald-900/60 mt-1">Prestasi bersifat penghargaan positif dan opsional.</p>

                    @if ($selectedSemester && $selectedClass && $students->isNotEmpty())
                        <form method="POST" action="{{ route('admin.achievements.store') }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">

                            <div>
                                <label for="student_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Pilih Siswa <span class="text-rose-500">*</span>
                                </label>
                                <select id="student_id"
                                        name="student_id"
                                        required
                                        class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none">
                                    <option value="">-- Pilih Siswa Kelas {{ $selectedClass->name }} --</option>
                                    @foreach ($students as $st)
                                        <option value="{{ $st->id }}" {{ old('student_id') == $st->id ? 'selected' : '' }}>
                                            {{ $st->name }} (NIS: {{ $st->nis }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="achievement_name" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Nama Kejuaraan / Prestasi <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       id="achievement_name"
                                       name="achievement_name"
                                       value="{{ old('achievement_name') }}"
                                       placeholder="Contoh: Lomba Tahfiz Al-Qur'an Juz 30"
                                       required
                                       class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="level" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                        Tingkat <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="level" name="level" required class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold">
                                        @foreach ($levels as $lvl)
                                            <option value="{{ $lvl }}" {{ old('level') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="activity_type" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                        Bidang <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="activity_type" name="activity_type" required class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold">
                                        @foreach ($activityTypes as $type)
                                            <option value="{{ $type }}" {{ old('activity_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="rank" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                        Peringkat / Juara <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="rank"
                                           name="rank"
                                           value="{{ old('rank') }}"
                                           placeholder="Juara 1, Emas, dll."
                                           required
                                           class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold">
                                </div>

                                <div>
                                    <label for="date" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                        Tanggal Kegiatan
                                    </label>
                                    <input type="date"
                                           id="date"
                                           name="date"
                                           value="{{ old('date') }}"
                                           class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold">
                                </div>
                            </div>

                            <div>
                                <label for="organizer" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Penyelenggara <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       id="organizer"
                                       name="organizer"
                                       value="{{ old('organizer') }}"
                                       placeholder="Contoh: Kemenag Kota, Lembaga LPPTKA"
                                       required
                                       class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold">
                            </div>

                            <div>
                                <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Keterangan / Catatan
                                </label>
                                <textarea id="notes"
                                          name="notes"
                                          rows="2"
                                          placeholder="Catatan tambahan..."
                                          class="mt-1.5 w-full rounded-xl border border-emerald-200 p-2 text-xs">{{ old('notes') }}</textarea>
                            </div>

                            <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-950/10 hover:bg-emerald-900 transition">
                                + Simpan Catatan Prestasi
                            </button>
                        </form>
                    @else
                        <div class="mt-4 rounded-2xl border border-dashed border-emerald-200 p-6 text-center text-xs text-emerald-900/60">
                            Pilih semester dan kelas untuk mulai mencatat prestasi siswa.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Daftar Prestasi Siswa -->
            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-emerald-100">
                        <div>
                            <h3 class="text-base font-bold text-emerald-950">Daftar Prestasi Siswa</h3>
                            <p class="text-xs text-emerald-900/60 mt-0.5">Penghargaan dan capaian siswa pada semester terpilih.</p>
                        </div>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-950">
                            🏆 {{ $achievements->total() }} Prestasi
                        </span>
                    </div>

                    <div class="mt-4 divide-y divide-emerald-100/60">
                        @forelse ($achievements as $ach)
                            <div class="py-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 hover:bg-emerald-50/20 px-2 rounded-xl transition">
                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-bold text-emerald-950">{{ $ach->student->name }}</span>
                                        <span class="text-[10px] font-mono text-emerald-900/60">NIS: {{ $ach->student->nis }}</span>
                                        <span class="rounded-full bg-amber-400/20 px-2 py-0.5 text-[10px] font-bold text-amber-900 border border-amber-300">
                                            {{ $ach->rank }}
                                        </span>
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                            Tingkat {{ $ach->level }}
                                        </span>
                                    </div>

                                    <h4 class="text-sm font-black text-emerald-950">
                                        {{ $ach->achievement_name }}
                                    </h4>

                                    <div class="text-xs text-emerald-900/70">
                                        Penyelenggara: <strong>{{ $ach->organizer }}</strong> • Bidang: {{ $ach->activity_type }}
                                        @if ($ach->date)
                                            • Tanggal: {{ \Carbon\Carbon::parse($ach->date)->translatedFormat('d M Y') }}
                                        @endif
                                    </div>

                                    @if ($ach->notes)
                                        <p class="text-xs text-emerald-900/60 italic">"{{ $ach->notes }}"</p>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('admin.achievements.destroy', $ach) }}" onsubmit="return confirm('Hapus catatan prestasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50 self-start">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="py-12 text-center text-sm text-emerald-900/50">
                                Belum ada catatan prestasi siswa untuk kriteria filter ini.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $achievements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

