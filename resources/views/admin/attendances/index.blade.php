@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Kehadiran Siswa')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Penilaian Nonakademik</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Kehadiran & Presensi Siswa</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Catat presensi harian secara massal dan pantau rekapitulasi kehadiran per semester.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('admin.non-academic-recap.index', ['semester_id' => $selectedSemester?->id, 'school_class_id' => $selectedClass?->id]) }}" class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-emerald-950 shadow-md shadow-amber-950/10 hover:bg-amber-300 transition">
                    <span>📋</span>
                    <span>Buka Rekap Nonakademik →</span>
                </a>
            </div>
        </div>

        <!-- Filter & Navigasi Tab -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm space-y-4">
            <!-- Tabs -->
            <div class="flex items-center gap-3 border-b border-emerald-100 pb-3">
                <a href="{{ route('admin.attendances.index', ['semester_id' => $selectedSemester?->id, 'school_class_id' => $selectedClass?->id, 'date' => $selectedDate, 'tab' => 'daily']) }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition {{ $tab === 'daily' ? 'bg-emerald-800 text-white shadow-sm' : 'text-emerald-900/70 hover:bg-emerald-50' }}">
                    <span>📅</span>
                    <span>Input Presensi Harian</span>
                </a>

                <a href="{{ route('admin.attendances.index', ['semester_id' => $selectedSemester?->id, 'school_class_id' => $selectedClass?->id, 'tab' => 'recap']) }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition {{ $tab === 'recap' ? 'bg-emerald-800 text-white shadow-sm' : 'text-emerald-900/70 hover:bg-emerald-50' }}">
                    <span>📊</span>
                    <span>Rekap Kehadiran Semester</span>
                </a>
            </div>

            <!-- Filter Controls -->
            <form method="GET" action="{{ route('admin.attendances.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <input type="hidden" name="tab" value="{{ $tab }}">

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

                @if ($tab === 'daily')
                    <div>
                        <label for="filter_date" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                            Tanggal Presensi
                        </label>
                        <input type="date"
                               id="filter_date"
                               name="date"
                               value="{{ $selectedDate }}"
                               onchange="this.form.submit()"
                               class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                @endif

                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-900 transition">
                        Tampilkan Data
                    </button>
                </div>
            </form>
        </div>

        @if (!$selectedSemester || !$selectedClass)
            <div class="rounded-3xl border border-dashed border-emerald-300 bg-emerald-50/50 p-12 text-center">
                <span class="text-4xl">📅</span>
                <h3 class="mt-3 text-base font-bold text-emerald-950">Pilih Semester dan Kelas</h3>
                <p class="text-xs text-emerald-900/60 mt-1">Pilih semester dan kelas untuk mengelola data kehadiran santri/siswa.</p>
            </div>
        @elseif ($students->isEmpty())
            <div class="rounded-3xl border border-emerald-950/10 bg-white p-12 text-center text-emerald-900/50">
                Tidak ada siswa yang terdaftar di Kelas {{ $selectedClass->name }} untuk semester ini.
            </div>
        @elseif ($tab === 'daily')
            <!-- TAB 1: INPUT PRESENSI HARIAN -->
            <form method="POST" action="{{ route('admin.attendances.store-daily') }}" id="attendance-form">
                @csrf
                <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                <input type="hidden" name="school_class_id" value="{{ $selectedClass->id }}">
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-emerald-100">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Presensi Harian</span>
                            <h2 class="text-xl font-black text-emerald-950">
                                Kelas {{ $selectedClass->name }} — {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}
                            </h2>
                            <p class="text-xs text-emerald-900/60 mt-0.5">
                                {{ $students->count() }} Siswa Terdaftar • Semester {{ $selectedSemester->term }} ({{ $selectedSemester->academicYear?->name }})
                            </p>
                        </div>

                        <!-- Tombol Cepat Set Semua Hadir -->
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="setAllAttendance('Hadir')" class="rounded-xl border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800 hover:bg-emerald-100">
                                ✓ Set Semua Hadir
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Presensi Harian -->
                    <div class="overflow-x-auto rounded-2xl border border-emerald-100">
                        <table class="w-full text-left text-sm text-emerald-950">
                            <thead class="border-b border-emerald-100 bg-emerald-50/70 text-xs uppercase font-bold text-emerald-900/80">
                                <tr>
                                    <th class="px-3 py-3 w-10 text-center">No</th>
                                    <th class="px-4 py-3 min-w-[200px]">Nama Siswa</th>
                                    <th class="px-3 py-3 min-w-[320px] text-center">Status Kehadiran</th>
                                    <th class="px-4 py-3 min-w-[200px]">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100/60">
                                @foreach ($students as $idx => $student)
                                    @php
                                        $currRecord = $dailyRecords[$student->id] ?? null;
                                        $currStatus = $currRecord['status'] ?? 'Hadir'; // Default hadir
                                        $currNote = $currRecord['notes'] ?? '';
                                    @endphp
                                    <tr class="hover:bg-emerald-50/30 transition student-att-row" data-student-id="{{ $student->id }}">
                                        <td class="px-3 py-3 text-center text-xs font-semibold text-emerald-900/60">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-emerald-950 text-xs sm:text-sm">{{ $student->name }}</div>
                                            <div class="text-[11px] font-mono text-emerald-900/60">NIS: {{ $student->nis }}</div>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <div class="inline-flex rounded-xl bg-emerald-50/80 p-1 border border-emerald-200">
                                                @foreach (['Hadir' => 'Hadir (H)', 'Sakit' => 'Sakit (S)', 'Izin' => 'Izin (I)', 'Alpa' => 'Alpa (A)'] as $val => $label)
                                                    <label class="cursor-pointer rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $currStatus === $val ? 'bg-emerald-800 text-white shadow-sm' : 'text-emerald-900/70 hover:bg-emerald-100' }}">
                                                        <input type="radio"
                                                               name="attendances[{{ $student->id }}]"
                                                               value="{{ $val }}"
                                                               {{ $currStatus === $val ? 'checked' : '' }}
                                                               onchange="highlightOption(this)"
                                                               class="sr-only att-radio">
                                                        <span>{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text"
                                                   name="notes[{{ $student->id }}]"
                                                   value="{{ $currNote }}"
                                                   placeholder="Keterangan sakit/izin..."
                                                   class="w-full rounded-xl border border-emerald-200 px-3 py-1.5 text-xs text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Action Bar -->
                    <div class="sticky bottom-4 z-20 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-emerald-950/20 bg-emerald-950 p-4 text-white shadow-2xl">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 place-items-center rounded-xl bg-amber-400 text-emerald-950 font-black">📅</span>
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-amber-400">Penyimpanan Transaksional</div>
                                <div class="text-xs text-emerald-100/80">Presensi disimpan otomatis dengan pencegahan data ganda per siswa & tanggal.</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit" class="rounded-xl bg-amber-400 px-6 py-2.5 text-xs font-black text-emerald-950 shadow-md shadow-amber-950/20 hover:bg-amber-300 transition">
                                Simpan Presensi Tanggal {{ $selectedDate }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <!-- TAB 2: REKAP KEHADIRAN SEMESTER -->
            <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-emerald-100">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Rekapitulasi Semester</span>
                        <h2 class="text-xl font-black text-emerald-950">
                            Kelas {{ $selectedClass->name }} — {{ $selectedSemester->academicYear?->name }} (Semester {{ $selectedSemester->term }})
                        </h2>
                        <p class="text-xs text-emerald-900/60 mt-0.5">
                            Total Hari Efektif Terdata: <strong class="text-emerald-900">{{ $totalRecordedDays }} hari</strong>
                        </p>
                    </div>

                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-950/20 bg-white px-4 py-2 text-xs font-bold text-emerald-900 hover:bg-emerald-50 print:hidden">
                        <span>🖨️</span>
                        <span>Cetak Rekap</span>
                    </button>
                </div>

                @if ($totalRecordedDays === 0)
                    <div class="rounded-2xl border border-dashed border-amber-300 bg-amber-50/50 p-8 text-center text-amber-900">
                        <span class="text-3xl">⚠️</span>
                        <h4 class="mt-2 text-sm font-bold">Belum Tersedia Data Kehadiran</h4>
                        <p class="mt-1 text-xs text-amber-900/70">
                            Belum ada presensi harian yang tercatat pada kelas dan semester ini.
                            Data tidak menampilkan angka nol yang seolah-olah sudah diverifikasi.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('admin.attendances.index', ['semester_id' => $selectedSemester->id, 'school_class_id' => $selectedClass->id, 'tab' => 'daily']) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-800 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-900">
                                Mulai Input Presensi Harian →
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Tabel Rekap Kehadiran Semester -->
                    <div class="overflow-x-auto rounded-2xl border border-emerald-100">
                        <table class="w-full text-left text-sm text-emerald-950">
                            <thead class="border-b border-emerald-100 bg-emerald-50/70 text-xs uppercase font-bold text-emerald-900/80">
                                <tr>
                                    <th class="px-3 py-3 w-10 text-center">No</th>
                                    <th class="px-4 py-3 min-w-[200px]">Nama Siswa</th>
                                    <th class="px-3 py-3 text-center w-24">Hadir (H)</th>
                                    <th class="px-3 py-3 text-center w-24">Sakit (S)</th>
                                    <th class="px-3 py-3 text-center w-24">Izin (I)</th>
                                    <th class="px-3 py-3 text-center w-24">Alpa (A)</th>
                                    <th class="px-3 py-3 text-center w-28 bg-emerald-100/50">Total Hari</th>
                                    <th class="px-3 py-3 text-center w-28">% Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100/60">
                                @foreach ($students as $idx => $student)
                                    @php
                                        $rec = $semesterRecap[$student->id] ?? null;
                                        $hasData = $rec && $rec['has_data'];
                                        $hadir = $hasData ? $rec['hadir'] : null;
                                        $sakit = $hasData ? $rec['sakit'] : null;
                                        $izin = $hasData ? $rec['izin'] : null;
                                        $alpa = $hasData ? $rec['alpa'] : null;
                                        $total = $hasData ? $rec['total'] : null;
                                        $pct = ($hasData && $total > 0) ? round(($hadir / $total) * 100, 1) : null;
                                    @endphp
                                    <tr class="hover:bg-emerald-50/30 transition">
                                        <td class="px-3 py-3 text-center text-xs font-semibold text-emerald-900/60">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-emerald-950 text-xs sm:text-sm">{{ $student->name }}</div>
                                            <div class="text-[11px] font-mono text-emerald-900/60">NIS: {{ $student->nis }}</div>
                                        </td>
                                        <td class="px-3 py-3 text-center font-bold text-emerald-800">
                                            {{ $hasData ? $hadir : '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-center font-semibold text-amber-700">
                                            {{ $hasData ? $sakit : '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-center font-semibold text-blue-700">
                                            {{ $hasData ? $izin : '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-center font-bold text-rose-700">
                                            {{ $hasData ? $alpa : '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-center font-bold bg-emerald-50/40">
                                            {{ $hasData ? $total : '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-center font-bold">
                                            @if ($pct !== null)
                                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $pct >= 85 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $pct }}%
                                                </span>
                                            @else
                                                <span class="text-xs text-emerald-900/40">Belum ada</span>
                                            @endif
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

    <script>
        function highlightOption(radio) {
            const container = radio.closest('td');
            const labels = container.querySelectorAll('label');
            labels.forEach(l => {
                const r = l.querySelector('input');
                if (r.checked) {
                    l.className = 'cursor-pointer rounded-lg px-2.5 py-1 text-xs font-bold transition bg-emerald-800 text-white shadow-sm';
                } else {
                    l.className = 'cursor-pointer rounded-lg px-2.5 py-1 text-xs font-bold transition text-emerald-900/70 hover:bg-emerald-100';
                }
            });
        }

        function setAllAttendance(targetStatus) {
            document.querySelectorAll('.student-att-row').forEach(row => {
                const radio = row.querySelector(`input[value="${targetStatus}"]`);
                if (radio) {
                    radio.checked = true;
                    highlightOption(radio);
                }
            });
        }
    </script>
@endsection

