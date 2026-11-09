@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Buku Leger Nilai Kelas')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <!-- Topbar Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Kurikulum & Penilaian</p>
            <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Buku Leger Nilai Kelas</h1>
            <p class="text-sm text-emerald-900/60 mt-0.5">
                Rekapitulasi lengkap nilai akademik seluruh mata pelajaran dan presensi per rombongan belajar.
            </p>
        </div>

        @if ($selectedSemester && $selectedClass && $ledgerData)
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.leger.export', ['semester_id' => $selectedSemester->id, 'school_class_id' => $selectedClass->id, 'sort_by_rank' => $sortByRank ? 1 : 0]) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-900 transition">
                    <span>📥</span>
                    <span>Ekspor CSV (Excel)</span>
                </a>
                <a href="{{ route('admin.leger.print', ['semester_id' => $selectedSemester->id, 'school_class_id' => $selectedClass->id, 'sort_by_rank' => $sortByRank ? 1 : 0, 'show_ranking' => $showRanking ? 1 : 0]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-white px-4 py-2.5 text-xs font-bold text-emerald-800 shadow-sm hover:bg-emerald-50 transition">
                    <span>🖨️</span>
                    <span>Cetak Leger</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Filter & Kontrol Status Penguncian Semester -->
    <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm space-y-4">
        <form method="GET" action="{{ route('admin.leger.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
            <div>
                <label for="semester_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70 mb-1">
                    Semester
                </label>
                <select name="semester_id" id="semester_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-emerald-200 bg-[#f5fbef] px-3.5 py-2.5 text-xs font-semibold text-emerald-950 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" {{ $selectedSemester && $selectedSemester->id === $sem->id ? 'selected' : '' }}>
                            {{ $sem->full_name }} {{ $sem->is_active ? '(Aktif)' : '' }} {{ $sem->isLocked() ? '🔒' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="school_class_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70 mb-1">
                    Kelas / Rombel
                </label>
                <select name="school_class_id" id="school_class_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-emerald-200 bg-[#f5fbef] px-3.5 py-2.5 text-xs font-semibold text-emerald-950 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    @forelse($availableClasses as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClass && $selectedClass->id === $cls->id ? 'selected' : '' }}>
                            Kelas {{ $cls->name }} (Tingkat {{ $cls->grade_level }})
                        </option>
                    @empty
                        <option value="">Tidak ada kelas</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label for="sort_by_rank" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70 mb-1">
                    Urutan Tampilan
                </label>
                <select name="sort_by_rank" id="sort_by_rank" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-emerald-200 bg-[#f5fbef] px-3.5 py-2.5 text-xs font-semibold text-emerald-950 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="0" {{ ! $sortByRank ? 'selected' : '' }}>Urut Berdasarkan Nama Siswa (A-Z)</option>
                    <option value="1" {{ $sortByRank ? 'selected' : '' }}>Urut Berdasarkan Peringkat (Tertinggi)</option>
                </select>
            </div>

            <div class="flex items-center gap-2 pb-2">
                <input type="checkbox" name="show_ranking" id="show_ranking" value="1" {{ $showRanking ? 'checked' : '' }} onchange="this.form.submit()"
                       class="rounded border-emerald-300 text-emerald-800 focus:ring-emerald-500">
                <label for="show_ranking" class="text-xs font-semibold text-emerald-950 cursor-pointer">
                    Tampilkan Kolom Peringkat
                </label>
            </div>
        </form>

        <!-- Status Penguncian Semester Banner -->
        @if ($selectedSemester)
            <div class="pt-3 border-t border-emerald-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    @if ($selectedSemester->isLocked())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800 border border-rose-200">
                            <span>🔒</span> Terkunci / Diarsipkan
                        </span>
                        <span class="text-xs text-rose-950">
                            Nilai dan draf rapor pada semester ini telah dikunci resmi. Penilaian dan penerbitan draf tidak dapat diubah sembarangan.
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                            <span>🔓</span> Terbuka / Aktif
                        </span>
                        <span class="text-xs text-emerald-900/70">
                            Periode penginputan nilai dan draf rapor masih terbuka untuk pembaruan data.
                        </span>
                    @endif
                </div>

                <div>
                    @if ($selectedSemester->isLocked())
                        <form method="POST" action="{{ route('admin.semesters.unlock', $selectedSemester) }}" onsubmit="return confirm('Buka kunci semester ini? Guru dan staf dapat mengubah nilai kembali.')">
                            @csrf
                            <button type="submit" class="rounded-xl border border-rose-300 bg-rose-50 px-3.5 py-1.5 text-xs font-bold text-rose-900 hover:bg-rose-100 transition">
                                🔓 Buka Kunci Semester
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.semesters.lock', $selectedSemester) }}" onsubmit="return confirm('Kunci dan arsipkan semester ini? Perubahan nilai dan draf akan dibatasi.')">
                            @csrf
                            <button type="submit" class="rounded-xl bg-rose-800 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-rose-900 transition shadow-sm">
                                🔒 Kunci & Arsipkan Semester
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if ($ledgerData && count($ledgerData['rows']) > 0)
        <!-- Ringkasan Statistik Kartu -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Total Siswa</span>
                <p class="text-2xl font-black text-emerald-950 mt-1">{{ count($ledgerData['rows']) }}</p>
                <span class="text-xs text-emerald-900/50">Peserta Didik di Rombel</span>
            </div>

            <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Mata Pelajaran</span>
                <p class="text-2xl font-black text-emerald-950 mt-1">{{ count($ledgerData['subjects']) }}</p>
                <span class="text-xs text-emerald-900/50">Mapel Ditugaskan</span>
            </div>

            <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Rata-Rata Kelas</span>
                <p class="text-2xl font-black text-emerald-900 mt-1">{{ data_get($ledgerData, 'statistics.overall_class_average', '0.0') }}</p>
                <span class="text-xs text-emerald-900/50">Seluruh Mata Pelajaran</span>
            </div>

            <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Nilai Tertinggi</span>
                <p class="text-2xl font-black text-amber-700 mt-1">{{ data_get($ledgerData, 'statistics.highest_average', '0.0') }}</p>
                <span class="text-xs text-emerald-900/50">Rata-Rata Maksimal</span>
            </div>
        </div>

        <!-- Tabel Leger Nilai Komprehensif -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white shadow-sm overflow-hidden">
            <div class="p-5 border-b border-emerald-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-emerald-950">
                        Rekapitulasi Nilai Kelas {{ $selectedClass->name }} — Semester {{ $selectedSemester->name }}
                    </h2>
                    <p class="text-xs text-emerald-900/60">
                        Wali Kelas: <strong>{{ data_get($ledgerData, 'teacher.teacher_name', 'Belum Ditugaskan') }}</strong>
                        @if(data_get($ledgerData, 'teacher.teacher_nip'))
                            (NIP. {{ data_get($ledgerData, 'teacher.teacher_nip') }})
                        @endif
                    </p>
                </div>
                <div class="text-xs text-emerald-900/60">
                    Format: Nilai Akhir Skala 0–100
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-emerald-950 text-white font-bold text-[10px] uppercase tracking-wider">
                            <th class="px-3 py-3 text-center border-r border-emerald-900/50 w-10">No</th>
                            <th class="px-3 py-3 border-r border-emerald-900/50 w-20">NIS</th>
                            <th class="px-4 py-3 border-r border-emerald-900/50 min-w-[180px]">Nama Siswa</th>

                            <!-- Kolom Mapel -->
                            @foreach($ledgerData['subjects'] as $subject)
                                <th class="px-3 py-3 text-center border-r border-emerald-900/50 min-w-[70px]" title="{{ $subject->name }}">
                                    {{ $subject->code }}
                                </th>
                            @endforeach

                            <th class="px-3 py-3 text-center border-r border-emerald-900/50 min-w-[75px] bg-emerald-900">Jumlah</th>
                            <th class="px-3 py-3 text-center border-r border-emerald-900/50 min-w-[75px] bg-emerald-900">Rata-Rata</th>

                            @if($showRanking)
                                <th class="px-3 py-3 text-center border-r border-emerald-900/50 min-w-[65px] bg-amber-500 text-emerald-950">Rangking</th>
                            @endif

                            <th class="px-2 py-3 text-center border-r border-emerald-900/50 w-10" title="Sakit">S</th>
                            <th class="px-2 py-3 text-center border-r border-emerald-900/50 w-10" title="Izin">I</th>
                            <th class="px-2 py-3 text-center w-10" title="Alpa / Tanpa Keterangan">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-100/60 text-emerald-950 font-medium">
                        @foreach($ledgerData['rows'] as $index => $row)
                            <tr class="hover:bg-emerald-50/50 transition">
                                <td class="px-3 py-2.5 text-center text-emerald-900/60 border-r border-emerald-100/60">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-3 py-2.5 font-mono text-emerald-900/80 border-r border-emerald-100/60">
                                    {{ $row['student']->nis }}
                                </td>
                                <td class="px-4 py-2.5 font-bold border-r border-emerald-100/60">
                                    {{ $row['student']->name }}
                                </td>

                                <!-- Nilai Per Mapel -->
                                @foreach($ledgerData['subjects'] as $subject)
                                    @php
                                        $sc = $row['scores'][$subject->id] ?? null;
                                    @endphp
                                    <td class="px-3 py-2.5 text-center border-r border-emerald-100/60 font-mono {{ $sc !== null && $sc < 70 ? 'text-rose-600 font-bold' : '' }}">
                                        {{ $sc !== null ? $sc : '—' }}
                                    </td>
                                @endforeach

                                <td class="px-3 py-2.5 text-center font-bold font-mono border-r border-emerald-100/60 bg-emerald-50/30">
                                    {{ $row['total_score'] }}
                                </td>
                                <td class="px-3 py-2.5 text-center font-black font-mono border-r border-emerald-100/60 bg-emerald-50/50 text-emerald-900">
                                    {{ number_format($row['average_score'], 1) }}
                                </td>

                                @if($showRanking)
                                    <td class="px-3 py-2.5 text-center font-black font-mono border-r border-emerald-100/60 bg-amber-50/80 text-amber-950">
                                        {{ $row['rank'] }}
                                    </td>
                                @endif

                                <td class="px-2 py-2.5 text-center font-mono border-r border-emerald-100/60 text-emerald-900/70">
                                    {{ $row['attendance']['sakit'] ?: '-' }}
                                </td>
                                <td class="px-2 py-2.5 text-center font-mono border-r border-emerald-100/60 text-emerald-900/70">
                                    {{ $row['attendance']['izin'] ?: '-' }}
                                </td>
                                <td class="px-2 py-2.5 text-center font-mono text-emerald-900/70">
                                    {{ $row['attendance']['alpa'] ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <!-- Baris Statistik Footer -->
                    <tfoot class="border-t-2 border-emerald-950/20 bg-[#f5fbef] font-bold text-[11px] text-emerald-950">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right uppercase border-r border-emerald-200">
                                Rata-Rata Kelas
                            </td>
                            @foreach($ledgerData['subjects'] as $subject)
                                <td class="px-3 py-3 text-center border-r border-emerald-200 font-mono">
                                    {{ $ledgerData['statistics']['subject_averages'][$subject->id] ?? '—' }}
                                </td>
                            @endforeach
                            <td class="px-3 py-3 border-r border-emerald-200"></td>
                            <td class="px-3 py-3 text-center font-black font-mono border-r border-emerald-200 text-emerald-900 bg-emerald-100/70">
                                {{ number_format($ledgerData['statistics']['overall_class_average'], 1) }}
                            </td>
                            @if($showRanking)
                                <td class="border-r border-emerald-200"></td>
                            @endif
                            <td colspan="3"></td>
                        </tr>

                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right uppercase border-r border-emerald-200 text-emerald-800">
                                Nilai Tertinggi
                            </td>
                            @foreach($ledgerData['subjects'] as $subject)
                                <td class="px-3 py-2 text-center border-r border-emerald-200 font-mono text-emerald-800">
                                    {{ $ledgerData['statistics']['subject_max'][$subject->id] ?? '—' }}
                                </td>
                            @endforeach
                            <td class="px-3 py-2 border-r border-emerald-200"></td>
                            <td class="px-3 py-2 text-center font-mono border-r border-emerald-200 text-emerald-800">
                                {{ number_format($ledgerData['statistics']['highest_average'], 1) }}
                            </td>
                            @if($showRanking)
                                <td class="border-r border-emerald-200"></td>
                            @endif
                            <td colspan="3"></td>
                        </tr>

                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right uppercase border-r border-emerald-200 text-rose-800">
                                Nilai Terendah
                            </td>
                            @foreach($ledgerData['subjects'] as $subject)
                                <td class="px-3 py-2 text-center border-r border-emerald-200 font-mono text-rose-800">
                                    {{ $ledgerData['statistics']['subject_min'][$subject->id] ?? '—' }}
                                </td>
                            @endforeach
                            <td class="px-3 py-2 border-r border-emerald-200"></td>
                            <td class="px-3 py-2 text-center font-mono border-r border-emerald-200 text-rose-800">
                                {{ number_format($ledgerData['statistics']['lowest_average'], 1) }}
                            </td>
                            @if($showRanking)
                                <td class="border-r border-emerald-200"></td>
                            @endif
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-3xl border border-dashed border-emerald-200 bg-white p-12 text-center space-y-2">
            <span class="inline-grid size-12 place-items-center rounded-2xl bg-amber-100 text-2xl">📋</span>
            <h3 class="text-lg font-bold text-emerald-950">Belum ada data siswa atau penugasan mapel</h3>
            <p class="text-xs text-emerald-900/60 max-w-md mx-auto">
                Silakan pastikan siswa telah ditempatkan pada rombel kelas ini dan mata pelajaran telah ditugaskan pada semester yang dipilih.
            </p>
        </div>
    @endif
</div>
@endsection
