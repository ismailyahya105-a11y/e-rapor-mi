@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Detail Nonakademik: ' . $student->name)

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.non-academic-recap.index', ['semester_id' => $selectedSemester?->id, 'school_class_id' => $enrollment?->school_class_id]) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                        ← Kembali ke Rekap Kelas
                    </a>
                    <span class="text-emerald-900/30">•</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Profil Nonakademik</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">{{ $student->name }}</h1>
                <p class="text-xs text-emerald-900/60 font-mono">
                    NIS: {{ $student->nis }} • NISN: {{ $student->nisn ?? '-' }} • Kelas: {{ $enrollment?->schoolClass?->name ?? 'Belum terdaftar' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto print:hidden">
                <form method="GET" action="{{ route('admin.non-academic-recap.show', $student) }}" class="flex items-center gap-2">
                    <select name="semester_id" onchange="this.form.submit()" class="rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none">
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $selectedSemester && $selectedSemester->id === $sem->id ? 'selected' : '' }}>
                                {{ $sem->academicYear?->name }} — Sem. {{ $sem->term }} {{ $sem->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-950/20 bg-white px-4 py-2 text-xs font-bold text-emerald-900 hover:bg-emerald-50 transition">
                    <span>🖨️</span>
                    <span>Cetak Lembar Portofolio</span>
                </button>
            </div>
        </div>

        <!-- 1. Rekap Kehadiran -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📅</span>
                    <h3 class="text-base font-bold text-emerald-950">1. Rekap Kehadiran Siswa</h3>
                </div>
                <span class="text-xs text-emerald-900/60">Semester {{ $selectedSemester->term }} ({{ $selectedSemester->academicYear?->name }})</span>
            </div>

            @if ($attendanceStats['has_data'])
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                    <div class="rounded-2xl bg-emerald-50/70 p-4 border border-emerald-100">
                        <span class="text-[11px] font-bold uppercase text-emerald-800">Hadir</span>
                        <div class="mt-1 text-2xl font-black text-emerald-900">{{ $attendanceStats['hadir'] }} hari</div>
                    </div>
                    <div class="rounded-2xl bg-amber-50/70 p-4 border border-amber-100">
                        <span class="text-[11px] font-bold uppercase text-amber-800">Sakit</span>
                        <div class="mt-1 text-2xl font-black text-amber-900">{{ $attendanceStats['sakit'] }} hari</div>
                    </div>
                    <div class="rounded-2xl bg-blue-50/70 p-4 border border-blue-100">
                        <span class="text-[11px] font-bold uppercase text-blue-800">Izin</span>
                        <div class="mt-1 text-2xl font-black text-blue-900">{{ $attendanceStats['izin'] }} hari</div>
                    </div>
                    <div class="rounded-2xl bg-rose-50/70 p-4 border border-rose-100">
                        <span class="text-[11px] font-bold uppercase text-rose-800">Alpa</span>
                        <div class="mt-1 text-2xl font-black text-rose-900">{{ $attendanceStats['alpa'] }} hari</div>
                    </div>
                    <div class="rounded-2xl bg-emerald-950 p-4 text-white">
                        <span class="text-[11px] font-bold uppercase text-amber-300">Persentase</span>
                        <div class="mt-1 text-2xl font-black text-white">
                            {{ $attendanceStats['total'] > 0 ? round(($attendanceStats['hadir'] / $attendanceStats['total']) * 100, 1) : 0 }}%
                        </div>
                    </div>
                </div>
            @else
                <p class="text-xs text-emerald-900/50 italic py-2">Belum tersedia data catatan presensi kehadiran pada semester ini.</p>
            @endif
        </div>

        <!-- 2. Perkembangan Sikap & Perilaku -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                <div class="flex items-center gap-2">
                    <span class="text-lg">⭐</span>
                    <h3 class="text-base font-bold text-emerald-950">2. Perkembangan Sikap dan Perilaku</h3>
                </div>
                <span class="text-xs text-emerald-900/60">{{ $attitudeRatings->count() }} Aspek Terdata</span>
            </div>

            @if ($attitudeRatings->isNotEmpty())
                <div class="overflow-x-auto rounded-2xl border border-emerald-100">
                    <table class="w-full text-left text-sm text-emerald-950">
                        <thead class="border-b border-emerald-100 bg-emerald-50/70 text-xs uppercase font-bold text-emerald-900/80">
                            <tr>
                                <th class="px-4 py-3 w-56">Aspek Pengamatan</th>
                                <th class="px-4 py-3 w-36 text-center">Predikat</th>
                                <th class="px-4 py-3">Catatan Perkembangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100/60">
                            @foreach ($attitudeRatings as $rat)
                                <tr>
                                    <td class="px-4 py-3 font-bold text-emerald-950">{{ $rat->aspect->name }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $rat->rating === 'Sangat Baik' ? 'bg-emerald-100 text-emerald-800' : ($rat->rating === 'Baik' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $rat->rating }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-emerald-950/80">
                                        {{ $rat->development_notes ?? 'Menunjukkan pembiasaan adab yang baik dalam kegiatan sehari-hari.' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($attitudeSummary)
                    <div class="rounded-2xl bg-emerald-50/60 p-4 border border-emerald-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-900/70">Catatan Rangkuman Sikap:</span>
                        <p class="mt-1 text-xs text-emerald-950 leading-relaxed font-medium">
                            "{{ $attitudeSummary->general_notes }}"
                        </p>
                    </div>
                @endif
            @else
                <p class="text-xs text-emerald-900/50 italic py-2">Belum ada penilaian sikap untuk siswa ini pada semester terpilih.</p>
            @endif
        </div>

        <!-- 3. Keaktifan Belajar Siswa -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                <div class="flex items-center gap-2">
                    <span class="text-lg">💡</span>
                    <h3 class="text-base font-bold text-emerald-950">3. Keaktifan Belajar di Kelas</h3>
                </div>
                <span class="text-xs text-emerald-900/60">{{ $activities->count() }} Observasi</span>
            </div>

            @if ($activities->isNotEmpty())
                <div class="divide-y divide-emerald-100/60">
                    @foreach ($activities as $act)
                        <div class="py-3 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900">
                                    {{ $act->aspect }}
                                </span>
                                <span class="text-[11px] text-emerald-900/50 font-mono">
                                    {{ \Carbon\Carbon::parse($act->date)->translatedFormat('d F Y') }}
                                </span>
                            </div>
                            <p class="text-xs text-emerald-950 font-medium">"{{ $act->description }}"</p>
                            @if ($act->teacher_notes)
                                <p class="text-[11px] text-emerald-800 italic">Catatan: {{ $act->teacher_notes }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-emerald-900/50 italic py-2">Belum ada catatan pengamatan keaktifan belajar siswa pada semester ini.</p>
            @endif
        </div>

        <div class="grid gap-8 lg:grid-cols-2">
            <!-- 4. Ekstrakurikuler -->
            <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⛺</span>
                        <h3 class="text-base font-bold text-emerald-950">4. Ekstrakurikuler</h3>
                    </div>
                    <span class="text-xs text-emerald-900/60">{{ $extracurriculars->count() }} Kegiatan</span>
                </div>

                @if ($extracurriculars->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($extracurriculars as $ex)
                            <div class="rounded-2xl border border-emerald-100 p-4 bg-emerald-50/20 space-y-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold text-emerald-950">{{ $ex->extracurricular->name }}</h4>
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                        {{ $ex->predicate }}
                                    </span>
                                </div>
                                <p class="text-xs text-emerald-900/80">{{ $ex->description ?? 'Aktif mengikuti seluruh pertemuan dan kegiatan.' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-emerald-900/50 italic py-2">Siswa belum terdaftar pada kegiatan ekstrakurikuler semester ini.</p>
                @endif
            </div>

            <!-- 5. Prestasi -->
            <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🏆</span>
                        <h3 class="text-base font-bold text-emerald-950">5. Prestasi & Penghargaan</h3>
                    </div>
                    <span class="text-xs text-emerald-900/60">{{ $achievements->count() }} Prestasi</span>
                </div>

                @if ($achievements->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($achievements as $ach)
                            <div class="rounded-2xl border border-amber-200 bg-amber-50/30 p-4 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="rounded-full bg-amber-400 px-2.5 py-0.5 text-[10px] font-black text-emerald-950">
                                        {{ $ach->rank }}
                                    </span>
                                    <span class="text-[10px] text-emerald-900/60 font-semibold">Tingkat {{ $ach->level }}</span>
                                </div>
                                <h4 class="text-xs font-black text-emerald-950 pt-1">{{ $ach->achievement_name }}</h4>
                                <div class="text-[11px] text-emerald-900/70">
                                    Penyelenggara: {{ $ach->organizer }}
                                    @if ($ach->date)
                                        • {{ \Carbon\Carbon::parse($ach->date)->translatedFormat('d M Y') }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-emerald-900/50 italic py-2">Belum ada catatan kejuaraan / prestasi (bersifat opsional).</p>
                @endif
            </div>
        </div>

        <!-- 6. Catatan Wali Kelas -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                <div class="flex items-center gap-2">
                    <span class="text-lg">✍️</span>
                    <h3 class="text-base font-bold text-emerald-950">6. Catatan Wali Kelas</h3>
                </div>
                <span class="text-xs text-emerald-900/60">Dicatat oleh: {{ $homeroomNote?->author?->name ?? 'Wali Kelas' }}</span>
            </div>

            @if ($homeroomNote)
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl bg-emerald-50/40 p-4 border border-emerald-100 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Perkembangan Belajar</span>
                        <p class="text-xs text-emerald-950">{{ $homeroomNote->academic_progress ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl bg-emerald-50/40 p-4 border border-emerald-100 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Sikap & Perilaku</span>
                        <p class="text-xs text-emerald-950">{{ $homeroomNote->attitude_behavior ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl bg-emerald-50/40 p-4 border border-emerald-100 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Kedisiplinan</span>
                        <p class="text-xs text-emerald-950">{{ $homeroomNote->discipline ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl bg-emerald-50/40 p-4 border border-emerald-100 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Potensi Siswa</span>
                        <p class="text-xs text-emerald-950">{{ $homeroomNote->student_potential ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl bg-amber-50/40 p-4 border border-amber-100 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Hal yang Perlu Ditingkatkan</span>
                        <p class="text-xs text-amber-950">{{ $homeroomNote->areas_for_improvement ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl bg-emerald-950 p-4 text-white space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-300">Saran Kepada Orang Tua</span>
                        <p class="text-xs text-emerald-100">{{ $homeroomNote->parent_advice ?? '-' }}</p>
                    </div>
                </div>
            @else
                <p class="text-xs text-emerald-900/50 italic py-2">Belum ada evaluasi catatan wali kelas untuk siswa ini pada semester terpilih.</p>
            @endif
        </div>
    </div>
@endsection

