@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Pengaturan Komponen Penilaian')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Manajemen Kurikulum & Asesmen</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Pengaturan Komponen Penilaian</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Atur pembobotan persentase komponen penilaian per mata pelajaran dan semester. Total bobot harus tepat 100%.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('admin.assessments.index') }}" class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-emerald-950 shadow-md shadow-amber-950/10 hover:bg-amber-300 transition">
                    <span>📝</span>
                    <span>Input Nilai Siswa →</span>
                </a>
                <a href="{{ route('admin.assessments.recap') }}" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-950/20 bg-white px-4 py-2.5 text-xs font-bold text-emerald-900 hover:bg-emerald-50 transition">
                    <span>📊</span>
                    <span>Rekap Nilai</span>
                </a>
            </div>
        </div>

        <!-- Selector Semester & Mata Pelajaran -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.assessments.components.index') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
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
                                {{ $sem->academicYear?->name ?? 'Tahun ?' }} — Semester {{ $sem->term }} {{ $sem->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter_subject" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Mata Pelajaran
                    </label>
                    <select id="filter_subject"
                            name="subject_id"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        @foreach ($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ $selectedSubject && $selectedSubject->id === $subj->id ? 'selected' : '' }}>
                                [{{ $subj->code }}] {{ $subj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-900 transition">
                        Tampilkan Komponen
                    </button>
                </div>
            </form>
        </div>

        @if ($selectedSemester && $selectedSubject)
            <!-- Status Bobot & Preset Bar -->
            <div class="grid gap-6 md:grid-cols-3">
                <!-- Status Bobot Card -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-900/60">Status Bobot Komponen</span>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-4xl font-black {{ abs($totalWeight - 100.0) < 0.001 ? 'text-emerald-700' : ($totalWeight > 100 ? 'text-rose-600' : 'text-amber-600') }}">
                                {{ number_format($totalWeight, 0) }}%
                            </span>
                            <span class="text-sm font-semibold text-emerald-900/60">/ 100%</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        @if (abs($totalWeight - 100.0) < 0.001)
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                <span>✓</span>
                                <span>Bobot Sempurna (100%) — Siap Input Nilai</span>
                            </div>
                        @elseif ($totalWeight < 100)
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                                <span>⚠️</span>
                                <span>Kurang {{ 100 - $totalWeight }}% untuk mencapai 100%</span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800">
                                <span>⛔</span>
                                <span>Kelebihan {{ $totalWeight - 100 }}%! Total tidak boleh melampaui 100%</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Presets Card (2 Kolom) -->
                <div class="md:col-span-2 rounded-3xl border border-emerald-950/10 bg-gradient-to-br from-emerald-900 to-emerald-950 p-6 text-white shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-amber-400 text-lg">⚡</span>
                            <h3 class="text-base font-bold text-white">Terapkan Preset Standar Kemenag / MI (100%)</h3>
                        </div>
                        <p class="text-xs text-emerald-100/70 mt-1">
                            Gunakan preset cepat untuk membentuk struktur penilaian lengkap 100% secara otomatis. Komponen lama yang belum memiliki nilai siswa akan disesuaikan.
                        </p>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <!-- Preset 1 -->
                        <form method="POST" action="{{ route('admin.assessments.components.preset') }}" onsubmit="return confirm('Terapkan Preset 3 Komponen (Tugas 40%, PTS 30%, PAS 30%)?');">
                            @csrf
                            <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                            <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                            <input type="hidden" name="preset_type" value="standard_3">
                            <button type="submit" class="w-full rounded-2xl border border-emerald-700 bg-emerald-800/80 p-3 text-left transition hover:bg-emerald-700/80 hover:border-amber-400/50">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black text-amber-300">PRESET 3 KOMPONEN</span>
                                    <span class="rounded-full bg-amber-400/20 px-2 py-0.5 text-[10px] font-bold text-amber-300">100%</span>
                                </div>
                                <p class="mt-1 text-xs text-emerald-100/90 font-medium">Tugas & Praktik (40%), PTS (30%), PAS (30%)</p>
                            </button>
                        </form>

                        <!-- Preset 2 -->
                        <form method="POST" action="{{ route('admin.assessments.components.preset') }}" onsubmit="return confirm('Terapkan Preset 4 Komponen (Tugas 20%, UH 30%, PTS 25%, PAS 25%)?');">
                            @csrf
                            <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                            <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                            <input type="hidden" name="preset_type" value="standard_4">
                            <button type="submit" class="w-full rounded-2xl border border-emerald-700 bg-emerald-800/80 p-3 text-left transition hover:bg-emerald-700/80 hover:border-amber-400/50">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black text-amber-300">PRESET 4 KOMPONEN</span>
                                    <span class="rounded-full bg-amber-400/20 px-2 py-0.5 text-[10px] font-bold text-amber-300">100%</span>
                                </div>
                                <p class="mt-1 text-xs text-emerald-100/90 font-medium">Tugas (20%), UH (30%), PTS (25%), PAS (25%)</p>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Konten Utama: Form Tambah & Tabel Komponen -->
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Form Tambah Komponen -->
                <div class="lg:col-span-1">
                    <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm sticky top-24">
                        <h2 class="text-lg font-bold text-emerald-950">Tambah Komponen</h2>
                        <p class="text-xs text-emerald-900/60 mt-1">
                            Untuk: <span class="font-bold text-emerald-900">[{{ $selectedSubject->code }}] {{ $selectedSubject->name }}</span>
                        </p>

                        <form method="POST" action="{{ route('admin.assessments.components.store') }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                            <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">

                            <div>
                                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Nama Komponen <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="Contoh: Tugas Harian, PTS, PAS"
                                       class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('name') border-rose-400 @enderror"
                                       required>
                                @error('name')
                                    <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="weight" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Bobot Persentase (%) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative mt-1.5">
                                    <input type="number"
                                           id="weight"
                                           name="weight"
                                           value="{{ old('weight', max(0, 100 - $totalWeight)) }}"
                                           min="0.01"
                                           max="100"
                                           step="0.01"
                                           placeholder="10 - 100"
                                           class="w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 pr-8 text-sm font-semibold focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('weight') border-rose-400 @enderror"
                                           required>
                                    <span class="pointer-events-none absolute right-3 top-2.5 text-xs font-bold text-emerald-900/50">%</span>
                                </div>
                                @error('weight')
                                    <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="order" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                    Nomor Urut Tampilan
                                </label>
                                <input type="number"
                                       id="order"
                                       name="order"
                                       value="{{ old('order', $components->count() + 1) }}"
                                       min="0"
                                       class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="size-4 rounded border-emerald-300 text-emerald-800 focus:ring-emerald-500">
                                <label for="is_active" class="text-xs font-semibold text-emerald-950">
                                    Aktif & digunakan dalam perhitungan nilai
                                </label>
                            </div>

                            <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-950/10 transition hover:bg-emerald-900">
                                + Tambah Komponen
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tabel Komponen Terdaftar -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-emerald-100">
                            <div>
                                <h3 class="text-base font-bold text-emerald-950">Daftar Komponen Terdaftar</h3>
                                <p class="text-xs text-emerald-900/60 mt-0.5">Komponen penilaian yang aktif akan menjadi kolom penilaian saat input nilai siswa.</p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">
                                {{ $components->count() }} Komponen
                            </span>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-sm text-emerald-950">
                                <thead class="border-b border-emerald-100 bg-emerald-50/60 text-xs uppercase font-bold text-emerald-900/70">
                                    <tr>
                                        <th class="px-4 py-3">Urut</th>
                                        <th class="px-4 py-3">Komponen</th>
                                        <th class="px-4 py-3 text-right">Bobot</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-emerald-100/60">
                                    @forelse ($components as $comp)
                                        <tr class="hover:bg-emerald-50/40">
                                            <td class="px-4 py-3 font-semibold text-emerald-900/60">{{ $comp->order }}</td>
                                            <td class="px-4 py-3 font-bold text-emerald-950">
                                                {{ $comp->name }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-mono font-bold text-emerald-900">
                                                {{ number_format($comp->weight, 2) }}%
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $comp->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                                    {{ $comp->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="inline-flex items-center gap-2">
                                                    <!-- Edit Dropdown / Details -->
                                                    <details class="relative text-left">
                                                        <summary class="cursor-pointer list-none rounded-lg border border-emerald-200 px-2.5 py-1 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
                                                            Ubah
                                                        </summary>
                                                        <div class="absolute right-0 top-8 z-30 w-72 rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-xl">
                                                            <h4 class="text-xs font-bold uppercase text-emerald-950">Edit {{ $comp->name }}</h4>
                                                            <form method="POST" action="{{ route('admin.assessments.components.update', $comp) }}" class="mt-3 space-y-3">
                                                                @csrf
                                                                @method('PUT')
                                                                <div>
                                                                    <label class="block text-[11px] font-bold text-emerald-900/70">Nama</label>
                                                                    <input type="text" name="name" value="{{ $comp->name }}" required class="mt-1 w-full rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-semibold">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[11px] font-bold text-emerald-900/70">Bobot (%)</label>
                                                                    <input type="number" name="weight" value="{{ $comp->weight }}" min="0.01" max="100" step="0.01" required class="mt-1 w-full rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-semibold">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[11px] font-bold text-emerald-900/70">Urut</label>
                                                                    <input type="number" name="order" value="{{ $comp->order }}" min="0" class="mt-1 w-full rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-semibold">
                                                                </div>
                                                                <div class="flex items-center gap-2">
                                                                    <input type="checkbox" name="is_active" id="active_{{ $comp->id }}" value="1" {{ $comp->is_active ? 'checked' : '' }} class="size-3.5 rounded border-emerald-300 text-emerald-800">
                                                                    <label for="active_{{ $comp->id }}" class="text-xs text-emerald-900">Aktif</label>
                                                                </div>
                                                                <div class="pt-2">
                                                                    <button type="submit" class="w-full rounded-lg bg-emerald-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-900">
                                                                        Simpan Perubahan
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </details>

                                                    <!-- Hapus Komponen -->
                                                    <form method="POST" action="{{ route('admin.assessments.components.destroy', $comp) }}" onsubmit="return confirm('Hapus komponen {{ $comp->name }}? Jika sudah memiliki nilai siswa, statusnya akan dinonaktifkan.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-sm text-emerald-900/50">
                                                Belum ada komponen penilaian untuk mata pelajaran ini pada semester terpilih.
                                                <br>Silakan terapkan <strong>Preset Standar</strong> di atas atau tambah komponen manual.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Ringkasan Kesiapan Seluruh Mapel -->
                    <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                            <div>
                                <h4 class="text-sm font-bold text-emerald-950">Status Kesiapan Penilaian Mata Pelajaran Lain</h4>
                                <p class="text-xs text-emerald-900/60 mt-0.5">Semester: {{ $selectedSemester->academicYear?->name }} — Semester {{ $selectedSemester->term }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach ($subjects as $s)
                                @php
                                    $summary = $subjectsSummary[$s->id] ?? ['weight' => 0, 'count' => 0, 'is_ready' => false];
                                @endphp
                                <a href="{{ route('admin.assessments.components.index', ['semester_id' => $selectedSemester->id, 'subject_id' => $s->id]) }}"
                                   class="flex items-center justify-between rounded-xl border {{ $s->id === $selectedSubject->id ? 'border-emerald-600 bg-emerald-50/60 ring-1 ring-emerald-600' : 'border-emerald-100 hover:border-emerald-300 hover:bg-emerald-50/20' }} p-3 transition">
                                    <div class="truncate pr-2">
                                        <div class="text-xs font-bold text-emerald-950 truncate">[{{ $s->code }}] {{ $s->name }}</div>
                                        <div class="text-[11px] text-emerald-900/60">{{ $summary['count'] }} komponen terdaftar</div>
                                    </div>
                                    <div>
                                        @if ($summary['is_ready'])
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                                100% Siap
                                            </span>
                                        @elseif ($summary['count'] > 0)
                                            <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">
                                                {{ number_format($summary['weight'], 0) }}%
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-500">
                                                Belum Diatur
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-emerald-300 bg-emerald-50/50 p-12 text-center">
                <span class="text-4xl">📚</span>
                <h3 class="mt-3 text-base font-bold text-emerald-950">Mata Pelajaran Belum Dipilih</h3>
                <p class="text-xs text-emerald-900/60 mt-1">Pilih semester dan mata pelajaran di atas untuk melihat dan mengatur komponen penilaian.</p>
            </div>
        @endif
    </div>
@endsection

