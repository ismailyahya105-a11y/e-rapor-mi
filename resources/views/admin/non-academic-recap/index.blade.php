@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Rekap Penilaian Nonakademik')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Laporan & Rekapitulasi</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Rekapitulasi Nonakademik Siswa</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Pantau rangkuman lengkap karakter, kehadiran, keaktifan, ekstrakurikuler, prestasi, dan catatan wali kelas.</p>
            </div>

            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-950/20 bg-white px-4 py-2.5 text-xs font-bold text-emerald-900 hover:bg-emerald-50 transition print:hidden self-start sm:self-auto">
                <span>🖨️</span>
                <span>Cetak Lembar Rekap</span>
            </button>
        </div>

        <!-- Filter Bar -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm print:hidden">
            <form method="GET" action="{{ route('admin.non-academic-recap.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                <span class="text-4xl">📋</span>
                <h3 class="mt-3 text-base font-bold text-emerald-950">Pilih Semester dan Kelas</h3>
                <p class="text-xs text-emerald-900/60 mt-1">Pilih semester dan kelas untuk melihat rangkuman data nonakademik santri/siswa.</p>
            </div>
        @elseif ($recapList->isEmpty())
            <div class="rounded-3xl border border-emerald-950/10 bg-white p-12 text-center text-emerald-900/50">
                Tidak ada data siswa yang ditemukan untuk kelas ini.
            </div>
        @else
            <!-- Tabel Rangkuman Nonakademik -->
            <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-emerald-100">
                    <div>
                        <h3 class="text-lg font-black text-emerald-950">
                            Lembar Rekap Nonakademik — Kelas {{ $selectedClass->name }}
                        </h3>
                        <p class="text-xs text-emerald-900/60 mt-0.5">
                            {{ $selectedSemester->academicYear?->name }} • Semester {{ $selectedSemester->term }} • {{ $recapList->count() }} Siswa
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-emerald-100">
                    <table class="w-full text-left text-sm text-emerald-950">
                        <thead class="border-b border-emerald-100 bg-emerald-50/70 text-xs uppercase font-bold text-emerald-900/80">
                            <tr>
                                <th class="px-3 py-3 w-10 text-center">No</th>
                                <th class="px-4 py-3 min-w-[180px]">Siswa</th>
                                <th class="px-3 py-3 min-w-[140px] text-center">Presensi (H / S / I / A)</th>
                                <th class="px-3 py-3 min-w-[140px] text-center">Penilaian Sikap</th>
                                <th class="px-3 py-3 min-w-[110px] text-center">Keaktifan</th>
                                <th class="px-3 py-3 min-w-[160px]">Ekstrakurikuler</th>
                                <th class="px-3 py-3 min-w-[90px] text-center">Prestasi</th>
                                <th class="px-3 py-3 min-w-[120px] text-center">Catatan Wali</th>
                                <th class="px-3 py-3 text-right print:hidden">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100/60">
                            @foreach ($recapList as $idx => $item)
                                @php
                                    $st = $item['student'];
                                    $att = $item['attendance'];
                                @endphp
                                <tr class="hover:bg-emerald-50/30 transition">
                                    <td class="px-3 py-3 text-center text-xs font-semibold text-emerald-900/60">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-emerald-950 text-xs sm:text-sm">{{ $st->name }}</div>
                                        <div class="text-[11px] font-mono text-emerald-900/60">NIS: {{ $st->nis }}</div>
                                    </td>

                                    <!-- Presensi -->
                                    <td class="px-3 py-3 text-center">
                                        @if ($att['has_data'])
                                            <div class="inline-flex items-center gap-1.5 font-mono text-xs font-bold">
                                                <span class="text-emerald-800" title="Hadir">{{ $att['hadir'] }}H</span>
                                                <span class="text-emerald-900/30">/</span>
                                                <span class="text-amber-700" title="Sakit">{{ $att['sakit'] }}S</span>
                                                <span class="text-emerald-900/30">/</span>
                                                <span class="text-blue-700" title="Izin">{{ $att['izin'] }}I</span>
                                                <span class="text-emerald-900/30">/</span>
                                                <span class="text-rose-700" title="Alpa">{{ $att['alpa'] }}A</span>
                                            </div>
                                        @else
                                            <span class="text-[11px] text-emerald-900/40 italic">Belum ada</span>
                                        @endif
                                    </td>

                                    <!-- Sikap -->
                                    <td class="px-3 py-3 text-center">
                                        @if ($item['attitude_count'] > 0)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                                {{ $item['attitude_count'] }} Aspek
                                            </span>
                                        @else
                                            <span class="text-[11px] text-emerald-900/40 italic">Belum diisi</span>
                                        @endif
                                    </td>

                                    <!-- Keaktifan -->
                                    <td class="px-3 py-3 text-center">
                                        @if ($item['activities_count'] > 0)
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-900">
                                                {{ $item['activities_count'] }} Catatan
                                            </span>
                                        @else
                                            <span class="text-emerald-900/40 text-xs">-</span>
                                        @endif
                                    </td>

                                    <!-- Ekstrakurikuler -->
                                    <td class="px-3 py-3">
                                        @if ($item['extras']->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($item['extras'] as $ex)
                                                    <span class="inline-flex rounded-lg bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-[10px] font-bold text-emerald-950">
                                                        {{ $ex->extracurricular->name }} ({{ $ex->predicate }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-[11px] text-emerald-900/40 italic">Belum ada</span>
                                        @endif
                                    </td>

                                    <!-- Prestasi -->
                                    <td class="px-3 py-3 text-center">
                                        @if ($item['achievements_count'] > 0)
                                            <span class="inline-flex rounded-full bg-amber-400/20 border border-amber-300 px-2.5 py-0.5 text-xs font-bold text-amber-900">
                                                🏆 {{ $item['achievements_count'] }}
                                            </span>
                                        @else
                                            <span class="text-emerald-900/40 text-xs">-</span>
                                        @endif
                                    </td>

                                    <!-- Catatan Wali Kelas -->
                                    <td class="px-3 py-3 text-center">
                                        @if ($item['has_homeroom_note'])
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                                ✓ Lengkap
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">
                                                Kosong
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-3 py-3 text-right print:hidden">
                                        <a href="{{ route('admin.non-academic-recap.show', ['student' => $st->id, 'semester_id' => $selectedSemester->id]) }}"
                                           class="inline-flex items-center gap-1 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-900 hover:bg-emerald-100 transition">
                                            <span>Rinci</span>
                                            <span>→</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

