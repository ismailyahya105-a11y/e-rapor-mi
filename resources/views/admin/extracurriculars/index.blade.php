@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Ekstrakurikuler')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Penilaian Nonakademik</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Kegiatan Ekstrakurikuler</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Kelola master kegiatan bakat minat dan catat perkembangan santri/siswa dalam ekstrakurikuler.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <details class="relative">
                    <summary class="cursor-pointer list-none inline-flex items-center gap-2 rounded-2xl border border-emerald-950/20 bg-white px-4 py-2.5 text-xs font-bold text-emerald-900 shadow-sm hover:bg-emerald-50 transition">
                        <span>➕</span>
                        <span>Tambah Master Ekskul</span>
                    </summary>
                    <div class="absolute right-0 top-12 z-30 w-80 rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-2xl space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-950">Tambah Kegiatan Baru</h3>
                        <form method="POST" action="{{ route('admin.extracurriculars.store-master') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-emerald-900/70">Nama Kegiatan</label>
                                <input type="text" name="name" placeholder="Misal: Tilawah Al-Qur'an, Futsal" required class="mt-1 w-full rounded-xl border border-emerald-200 px-3 py-1.5 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-emerald-900/70">Kategori</label>
                                <select name="category" class="mt-1 w-full rounded-xl border border-emerald-200 px-3 py-1.5 text-xs font-semibold">
                                    <option value="Keagamaan">Keagamaan</option>
                                    <option value="Kepanduan">Kepanduan (Pramuka)</option>
                                    <option value="Olahraga">Olahraga</option>
                                    <option value="Seni & Budaya">Seni & Budaya</option>
                                    <option value="Akademik">Akademik / Sains</option>
                                    <option value="Kesehatan">Kesehatan (UKS)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-emerald-900/70">Deskripsi Ringkas</label>
                                <textarea name="description" rows="2" placeholder="Fokus kegiatan..." class="mt-1 w-full rounded-xl border border-emerald-200 p-2 text-xs"></textarea>
                            </div>
                            <button type="submit" class="w-full rounded-xl bg-emerald-800 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-900">
                                + Simpan Master Ekskul
                            </button>
                        </form>
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
            <form method="GET" action="{{ route('admin.extracurriculars.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                    <label for="filter_extra" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Filter Jenis Ekstrakurikuler
                    </label>
                    <select id="filter_extra"
                            name="extracurricular_id"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <option value="">Semua Kegiatan Ekskul</option>
                        @foreach ($extracurriculars as $ex)
                            <option value="{{ $ex->id }}" {{ $selectedExtraId == $ex->id ? 'selected' : '' }}>
                                {{ $ex->name }} ({{ $ex->category }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Form Input Partisipasi Ekskul Siswa -->
            <div class="lg:col-span-1">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm sticky top-24">
                    <h2 class="text-lg font-bold text-emerald-950">Catat Ekskul Siswa</h2>
                    <p class="text-xs text-emerald-900/60 mt-1">Satu siswa dapat mengikuti lebih dari satu kegiatan ekstrakurikuler.</p>

                    @if ($selectedSemester && $selectedClass && $students->isNotEmpty() && $activeExtracurriculars->isNotEmpty())
                        <form method="POST" action="{{ route('admin.extracurriculars.store-participation') }}" class="mt-5 space-y-4">
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
                                <label for="extracurricular_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Kegiatan Ekstrakurikuler <span class="text-rose-500">*</span>
                                </label>
                                <select id="extracurricular_id"
                                        name="extracurricular_id"
                                        required
                                        class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none">
                                    @foreach ($activeExtracurriculars as $ex)
                                        <option value="{{ $ex->id }}" {{ old('extracurricular_id') == $ex->id ? 'selected' : '' }}>
                                            {{ $ex->name }} [{{ $ex->category }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="predicate" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Predikat Capaian <span class="text-rose-500">*</span>
                                </label>
                                <select id="predicate"
                                        name="predicate"
                                        required
                                        class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-bold text-emerald-950 focus:border-emerald-600 focus:outline-none">
                                    <option value="Sangat Baik">Sangat Baik</option>
                                    <option value="Baik" selected>Baik</option>
                                    <option value="Cukup">Cukup</option>
                                </select>
                            </div>

                            <div>
                                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Keterangan Perkembangan
                                </label>
                                <textarea id="description"
                                          name="description"
                                          rows="3"
                                          placeholder="Contoh: Menguasai teknik dasar azan dan tajwid makharijul huruf dengan baik..."
                                          class="mt-1.5 w-full rounded-xl border border-emerald-200 p-2.5 text-xs text-emerald-950 focus:border-emerald-600 focus:outline-none">{{ old('description') }}</textarea>
                            </div>

                            <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-950/10 hover:bg-emerald-900 transition">
                                + Simpan Capaian Ekskul
                            </button>
                        </form>
                    @else
                        <div class="mt-4 rounded-2xl border border-dashed border-emerald-200 p-6 text-center text-xs text-emerald-900/60">
                            Pilih semester dan kelas, serta pastikan terdapat master ekstrakurikuler yang aktif.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Daftar Partisipasi Ekskul -->
            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-emerald-100">
                        <div>
                            <h3 class="text-base font-bold text-emerald-950">Daftar Keikutsertaan Ekskul</h3>
                            <p class="text-xs text-emerald-900/60 mt-0.5">Catatan capaian kegiatan ekstrakurikuler semester terpilih.</p>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                            {{ $participations->total() }} Data
                        </span>
                    </div>

                    <div class="mt-4 divide-y divide-emerald-100/60">
                        @forelse ($participations as $part)
                            <div class="py-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 hover:bg-emerald-50/20 px-2 rounded-xl transition">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-emerald-950">{{ $part->student->name }}</span>
                                        <span class="text-[10px] font-mono text-emerald-900/60">NIS: {{ $part->student->nis }}</span>
                                        <span class="rounded-full bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-[10px] font-bold text-emerald-900">
                                            {{ $part->extracurricular->name }}
                                        </span>
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900">
                                            {{ $part->predicate }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-emerald-950 leading-relaxed font-medium">
                                        {{ $part->description ?? 'Aktif mengikuti seluruh rangkaian kegiatan ekstrakurikuler.' }}
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('admin.extracurriculars.destroy-participation', $part) }}" onsubmit="return confirm('Hapus keikutsertaan ekskul ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50 self-start">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="py-12 text-center text-sm text-emerald-900/50">
                                Belum ada data keikutsertaan ekstrakurikuler siswa untuk filter ini.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $participations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

