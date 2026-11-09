@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Keaktifan Belajar Siswa')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Penilaian Nonakademik</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Keaktifan Belajar Siswa</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Dokumentasikan keterlibatan aktif, partisipasi tanya jawab, dan inisiatif belajar santri/siswa.</p>
            </div>

            <a href="{{ route('admin.non-academic-recap.index', ['semester_id' => $selectedSemester?->id, 'school_class_id' => $selectedClass?->id]) }}" class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-emerald-950 shadow-md shadow-amber-950/10 hover:bg-amber-300 transition self-start sm:self-auto">
                <span>📋</span>
                <span>Buka Rekap Nonakademik →</span>
            </a>
        </div>

        <!-- Filter Bar -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.activities.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                    <label for="filter_aspect" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Aspek Keaktifan
                    </label>
                    <select id="filter_aspect"
                            name="aspect"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <option value="">Semua Aspek</option>
                        @foreach ($standardAspects as $asp)
                            <option value="{{ $asp }}" {{ $aspectFilter === $asp ? 'selected' : '' }}>{{ $asp }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter_search" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Cari Catatan
                    </label>
                    <div class="mt-1.5 flex gap-2">
                        <input type="text"
                               id="filter_search"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Nama siswa atau deskripsi..."
                               class="w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <button type="submit" class="rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-900 transition">
                            Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Form Catat Keaktifan -->
            <div class="lg:col-span-1">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm sticky top-24">
                    <h2 class="text-lg font-bold text-emerald-950">Catat Pengamatan Baru</h2>
                    <p class="text-xs text-emerald-900/60 mt-1">Pengamatan harus berdasarkan fakta nyata di kelas.</p>

                    @if ($selectedSemester && $selectedClass && $students->isNotEmpty())
                        <form method="POST" action="{{ route('admin.activities.store') }}" class="mt-5 space-y-4">
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
                                <label for="date" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Tanggal Pengamatan <span class="text-rose-500">*</span>
                                </label>
                                <input type="date"
                                       id="date"
                                       name="date"
                                       value="{{ old('date', date('Y-m-d')) }}"
                                       required
                                       class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none">
                            </div>

                            <div>
                                <label for="aspect" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Aspek Keaktifan <span class="text-rose-500">*</span>
                                </label>
                                <select id="aspect"
                                        name="aspect"
                                        required
                                        class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none">
                                    @foreach ($standardAspects as $asp)
                                        <option value="{{ $asp }}" {{ old('aspect') === $asp ? 'selected' : '' }}>{{ $asp }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Deskripsi Pengamatan Nyata <span class="text-rose-500">*</span>
                                </label>
                                <textarea id="description"
                                          name="description"
                                          rows="3"
                                          placeholder="Contoh: Sangat aktif mengajukan pertanyaan kritis saat materi tajwid surat Al-Fil..."
                                          required
                                          class="mt-1.5 w-full rounded-xl border border-emerald-200 p-2.5 text-xs text-emerald-950 focus:border-emerald-600 focus:outline-none">{{ old('description') }}</textarea>
                            </div>

                            <div>
                                <label for="teacher_notes" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Catatan Guru (Opsional)
                                </label>
                                <input type="text"
                                       id="teacher_notes"
                                       name="teacher_notes"
                                       value="{{ old('teacher_notes') }}"
                                       placeholder="Tindak lanjut atau apresiasi..."
                                       class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs text-emerald-950 focus:border-emerald-600 focus:outline-none">
                            </div>

                            <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-950/10 hover:bg-emerald-900 transition">
                                + Simpan Pengamatan Keaktifan
                            </button>
                        </form>
                    @else
                        <div class="mt-4 rounded-2xl border border-dashed border-emerald-200 p-6 text-center text-xs text-emerald-900/60">
                            Pilih semester dan kelas yang memiliki siswa untuk mulai mencatat pengamatan.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Daftar Catatan Keaktifan -->
            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-emerald-100">
                        <div>
                            <h3 class="text-base font-bold text-emerald-950">Riwayat Pengamatan Keaktifan</h3>
                            <p class="text-xs text-emerald-900/60 mt-0.5">Daftar observasi keaktifan belajar siswa pada semester terpilih.</p>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                            {{ $activities->total() }} Catatan
                        </span>
                    </div>

                    <div class="mt-4 divide-y divide-emerald-100/60">
                        @forelse ($activities as $act)
                            <div class="py-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 hover:bg-emerald-50/20 px-2 rounded-xl transition">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-emerald-950">{{ $act->student->name }}</span>
                                        <span class="text-[10px] font-mono text-emerald-900/60">NIS: {{ $act->student->nis }}</span>
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900">
                                            {{ $act->aspect }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-emerald-950 leading-relaxed font-medium">
                                        "{{ $act->description }}"
                                    </p>

                                    @if ($act->teacher_notes)
                                        <div class="text-[11px] text-emerald-800 italic">
                                            Catatan Guru: {{ $act->teacher_notes }}
                                        </div>
                                    @endif

                                    <div class="text-[10px] text-emerald-900/50">
                                        📅 {{ \Carbon\Carbon::parse($act->date)->translatedFormat('d M Y') }} • Dicatat oleh: {{ $act->recorder?->name ?? 'Admin' }}
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.activities.destroy', $act) }}" onsubmit="return confirm('Hapus catatan keaktifan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50 self-start">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="py-12 text-center text-sm text-emerald-900/50">
                                Belum ada catatan keaktifan belajar siswa untuk kriteria filter ini.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

