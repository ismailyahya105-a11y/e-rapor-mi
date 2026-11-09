@extends('layouts.dashboard', ['isAdmin' => false])

@section('title', 'Buku Rapor — ' . $student->name)

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <!-- Top Nav & Breadcrumb -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('wali.children.show', $student) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                ← Kembali ke Informasi Anak
            </a>
            <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl mt-1">
                Buku Rapor — {{ $student->name }}
            </h1>
            <p class="text-sm text-emerald-900/60 mt-0.5">
                Semester {{ $semester->name }} • Tahun Ajaran {{ $semester->academicYear->name }} • NIS: <span class="font-mono font-bold">{{ $student->nis }}</span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('wali.reports.download', $publication) }}"
               class="inline-flex items-center gap-2 rounded-2xl bg-emerald-800 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-950/15 hover:bg-emerald-900 transition">
                <span>📄</span>
                <span>Unduh PDF Resmi</span>
            </a>
        </div>
    </div>

    <!-- Status Banner Publikasi Resmi -->
    <div class="rounded-3xl border border-emerald-300 bg-gradient-to-r from-emerald-50 via-white to-emerald-50/50 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-700 px-3 py-1 text-xs font-bold text-white">
                    <span>✓</span> Terbit Resmi
                </span>
                <span class="text-xs font-semibold text-emerald-900/70">
                    Dokumen Sah Hasil Belajar Siswa
                </span>
            </div>
            <p class="text-xs text-emerald-900/80 pt-1">
                Rapor ini telah disahkan dan diterbitkan pada <strong>{{ $publication->published_at ? $publication->published_at->translatedFormat('d F Y \p\u\k\u\l H:i') : '—' }} WIB</strong>.
            </p>
        </div>
        <a href="{{ route('wali.reports.download', $publication) }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-400 px-4 py-2.5 text-xs font-black text-emerald-950 shadow-sm hover:bg-amber-300 transition">
            <span>📥</span>
            <span>Simpan PDF ke Perangkat</span>
        </a>
    </div>

    <!-- Konten Rapor -->
    <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 sm:p-8 space-y-8 shadow-sm">
        <!-- Identitas Madrasah & Siswa -->
        <div class="border-b border-emerald-100 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Kementerian Agama RI</span>
                    <h2 class="text-xl font-bold text-emerald-950">{{ data_get($snapshot, 'school.name', 'Madrasah Ibtidaiyah') }}</h2>
                    <p class="text-xs text-emerald-900/60 mt-0.5">
                        NSM: {{ data_get($snapshot, 'school.nsm', '-') }} | NPSN: {{ data_get($snapshot, 'school.npsn', '-') }}
                    </p>
                </div>
                <div class="text-left sm:text-right text-xs text-emerald-900/70 space-y-0.5">
                    <p>Kelas: <strong>{{ data_get($snapshot, 'enrollment.class_name', '-') }}</strong></p>
                    <p>Fase: <strong>{{ (int) data_get($snapshot, 'enrollment.grade_level', 1) <= 2 ? 'Fase A' : ((int) data_get($snapshot, 'enrollment.grade_level', 1) <= 4 ? 'Fase B' : 'Fase C') }}</strong></p>
                    <p>Tahun Ajaran: <strong>{{ data_get($snapshot, 'enrollment.academic_year', '-') }}</strong></p>
                </div>
            </div>
        </div>

        <!-- A. Nilai Hasil Belajar (Akademik) -->
        <section class="space-y-3">
            <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                <span class="grid size-6 place-items-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-800">A</span>
                <span>Nilai Capaian Belajar (Akademik)</span>
            </h3>
            <div class="overflow-x-auto rounded-2xl border border-emerald-100">
                <table class="w-full text-left text-xs">
                    <thead class="bg-emerald-50/70 text-emerald-900 font-bold uppercase text-[10px] tracking-wider border-b border-emerald-100">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3">Mata Pelajaran</th>
                            <th class="px-4 py-3 text-center w-24">Nilai Akhir</th>
                            <th class="px-4 py-3">Capaian Kompetensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-100/60">
                        @forelse(data_get($snapshot, 'academics', []) as $index => $item)
                            <tr class="hover:bg-emerald-50/30">
                                <td class="px-4 py-3 text-center text-emerald-900/60">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-bold text-emerald-950">{{ $item['subject'] }}</td>
                                <td class="px-4 py-3 text-center font-bold text-emerald-900 text-sm">
                                    {{ $item['final_score'] ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-emerald-900/80 leading-relaxed">
                                    {{ $item['description'] ?: 'Telah menyelesaikan target pembelajaran dengan capaian yang baik.' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-emerald-900/50 italic">
                                    Belum ada data mata pelajaran pada draf ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- B. Penilaian Sikap & Perilaku -->
        <section class="space-y-3">
            <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                <span class="grid size-6 place-items-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-800">B</span>
                <span>Penilaian Sikap dan Perilaku</span>
            </h3>

            @if(data_get($snapshot, 'attitude_summary'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 text-xs text-emerald-900 leading-relaxed">
                    <strong class="block text-emerald-950 mb-1">Catatan Perkembangan Sikap & Spiritual:</strong>
                    {{ data_get($snapshot, 'attitude_summary') }}
                </div>
            @endif

            @if(!empty(data_get($snapshot, 'attitudes', [])))
                <div class="overflow-x-auto rounded-2xl border border-emerald-100">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-emerald-50/70 text-emerald-900 font-bold uppercase text-[10px] tracking-wider border-b border-emerald-100">
                            <tr>
                                <th class="px-4 py-2.5 text-center w-12">No</th>
                                <th class="px-4 py-2.5">Aspek Karakter</th>
                                <th class="px-4 py-2.5 text-center w-28">Predikat</th>
                                <th class="px-4 py-2.5">Catatan Perkembangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100/60">
                            @foreach(data_get($snapshot, 'attitudes', []) as $idx => $att)
                                <tr class="hover:bg-emerald-50/30">
                                    <td class="px-4 py-2.5 text-center text-emerald-900/60">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-2.5 font-bold text-emerald-950">{{ $att['aspect'] }}</td>
                                    <td class="px-4 py-2.5 text-center font-bold text-emerald-800">{{ $att['rating'] }}</td>
                                    <td class="px-4 py-2.5 text-emerald-900/80">{{ $att['notes'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- C. Rekapitulasi Kehadiran -->
        <section class="space-y-3">
            <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                <span class="grid size-6 place-items-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-800">C</span>
                <span>Rekapitulasi Kehadiran Siswa</span>
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-2xl border border-emerald-100 bg-[#f5fbef] p-3 text-center">
                    <span class="text-[10px] uppercase font-bold text-emerald-800/70">Hadir</span>
                    <p class="text-xl font-black text-emerald-950 mt-0.5">{{ data_get($snapshot, 'attendance.hadir', 0) }}</p>
                    <span class="text-[10px] text-emerald-900/50">Hari</span>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-[#f5fbef] p-3 text-center">
                    <span class="text-[10px] uppercase font-bold text-emerald-800/70">Sakit</span>
                    <p class="text-xl font-black text-emerald-950 mt-0.5">{{ data_get($snapshot, 'attendance.sakit', 0) }}</p>
                    <span class="text-[10px] text-emerald-900/50">Hari</span>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-[#f5fbef] p-3 text-center">
                    <span class="text-[10px] uppercase font-bold text-emerald-800/70">Izin</span>
                    <p class="text-xl font-black text-emerald-950 mt-0.5">{{ data_get($snapshot, 'attendance.izin', 0) }}</p>
                    <span class="text-[10px] text-emerald-900/50">Hari</span>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-[#f5fbef] p-3 text-center">
                    <span class="text-[10px] uppercase font-bold text-emerald-800/70">Tanpa Keterangan</span>
                    <p class="text-xl font-black text-emerald-950 mt-0.5">{{ data_get($snapshot, 'attendance.alpa', 0) }}</p>
                    <span class="text-[10px] text-emerald-900/50">Hari</span>
                </div>
            </div>
        </section>

        <!-- D. Ekstrakurikuler & E. Prestasi -->
        <div class="grid gap-6 sm:grid-cols-2">
            <section class="space-y-3">
                <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                    <span class="grid size-6 place-items-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-800">D</span>
                    <span>Ekstrakurikuler</span>
                </h3>
                <div class="rounded-2xl border border-emerald-100 p-4 space-y-3 bg-[#f5fbef]">
                    @forelse(data_get($snapshot, 'extracurriculars', []) as $extra)
                        <div class="flex items-start justify-between border-b border-emerald-100/80 pb-2 last:border-none last:pb-0 text-xs">
                            <div>
                                <strong class="text-emerald-950">{{ $extra['name'] }}</strong>
                                <p class="text-[11px] text-emerald-900/70 mt-0.5">{{ $extra['description'] }}</p>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 shrink-0">
                                {{ $extra['predicate'] }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-emerald-900/50 italic">Tidak ada kegiatan ekstrakurikuler yang diikuti.</p>
                    @endforelse
                </div>
            </section>

            <section class="space-y-3">
                <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                    <span class="grid size-6 place-items-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-800">E</span>
                    <span>Prestasi Siswa</span>
                </h3>
                <div class="rounded-2xl border border-emerald-100 p-4 space-y-3 bg-[#f5fbef]">
                    @forelse(data_get($snapshot, 'achievements', []) as $ach)
                        <div class="border-b border-emerald-100/80 pb-2 last:border-none last:pb-0 text-xs">
                            <div class="flex items-center justify-between">
                                <strong class="text-emerald-950">{{ $ach['achievement_name'] }}</strong>
                                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-900">
                                    {{ $ach['rank'] }}
                                </span>
                            </div>
                            <p class="text-[11px] text-emerald-900/60 mt-0.5">
                                Tingkat: {{ $ach['level'] }} • Oleh: {{ $ach['organizer'] }}
                            </p>
                        </div>
                    @empty
                        <p class="text-xs text-emerald-900/50 italic">Tidak ada catatan prestasi pada semester ini.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- F. Catatan Wali Kelas -->
        <section class="space-y-3">
            <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                <span class="grid size-6 place-items-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-800">F</span>
                <span>Catatan Perkembangan & Saran Wali Kelas</span>
            </h3>
            <div class="rounded-2xl border border-emerald-100 bg-[#f5fbef] p-5 space-y-3 text-xs leading-relaxed">
                <div>
                    <strong class="text-emerald-950 block">Perkembangan Akademik:</strong>
                    <p class="text-emerald-900/80 mt-0.5">{{ data_get($snapshot, 'homeroom_note.academic_progress', '-') }}</p>
                </div>
                <div>
                    <strong class="text-emerald-950 block">Sikap & Perilaku:</strong>
                    <p class="text-emerald-900/80 mt-0.5">{{ data_get($snapshot, 'homeroom_note.attitude_behavior', '-') }}</p>
                </div>
                <div>
                    <strong class="text-emerald-950 block">Potensi & Bakat Siswa:</strong>
                    <p class="text-emerald-900/80 mt-0.5">{{ data_get($snapshot, 'homeroom_note.student_potential', '-') }}</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-3 border border-amber-200">
                    <strong class="text-amber-950 block">Saran & Nasihat untuk Orang Tua/Wali:</strong>
                    <p class="text-amber-900/90 mt-0.5">{{ data_get($snapshot, 'homeroom_note.parent_advice', '-') }}</p>
                </div>
            </div>
        </section>

        <!-- Footer Pengesahan -->
        <div class="border-t border-emerald-100 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-emerald-900/70 gap-4">
            <div>
                Wali Kelas: <strong>{{ data_get($snapshot, 'signatories.homeroom_teacher.teacher_name', '-') }}</strong>
                @if(data_get($snapshot, 'signatories.homeroom_teacher.teacher_nip'))
                    (NIP. {{ data_get($snapshot, 'signatories.homeroom_teacher.teacher_nip') }})
                @endif
            </div>
            <div>
                Kepala Madrasah: <strong>{{ data_get($snapshot, 'signatories.principal.principal_name', '-') }}</strong>
                @if(data_get($snapshot, 'signatories.principal.principal_nip'))
                    (NIP. {{ data_get($snapshot, 'signatories.principal.principal_nip') }})
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
