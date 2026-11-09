@extends('layouts.dashboard', ['isAdmin' => false])

@section('title', 'Informasi Akademik — ' . $student->name)

@section('content')
    <div class="mx-auto max-w-5xl space-y-8">
        <!-- Top Nav -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('wali.dashboard') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                    ← Kembali ke Daftar Anak
                </a>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl mt-1">{{ $student->name }}</h1>
                <p class="text-sm text-emerald-900/60 mt-0.5">NIS: <span class="font-mono font-bold">{{ $student->nis }}</span> | NISN: <span class="font-mono">{{ $student->nisn ?? '-' }}</span></p>
            </div>
            <span class="rounded-full bg-emerald-100 px-3.5 py-1 text-xs font-bold text-emerald-800">
                Status: {{ ucfirst($student->status) }}
            </span>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Profil Identitas Anak -->
            <div class="lg:col-span-1 space-y-6">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-4 border-b border-emerald-100 pb-5">
                        <span class="grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-amber-200 to-amber-300 text-2xl font-black text-emerald-950 shadow-sm">
                            {{ strtoupper(mb_substr($student->name, 0, 1)) }}
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-emerald-950 leading-snug">{{ $student->name }}</h2>
                            <span class="text-xs text-emerald-900/60">{{ $student->gender }}</span>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-3.5 text-xs">
                        <div>
                            <dt class="font-bold uppercase tracking-wider text-emerald-900/60">Nomor Induk Siswa (NIS)</dt>
                            <dd class="mt-0.5 font-mono text-sm font-bold text-emerald-950">{{ $student->nis }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold uppercase tracking-wider text-emerald-900/60">NISN</dt>
                            <dd class="mt-0.5 font-mono text-sm text-emerald-950">{{ $student->nisn ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold uppercase tracking-wider text-emerald-900/60">Tempat, Tanggal Lahir</dt>
                            <dd class="mt-0.5 text-sm font-medium text-emerald-950">
                                {{ $student->birth_place ?? '-' }}, {{ $student->birth_date ? $student->birth_date->translatedFormat('d F Y') : '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-bold uppercase tracking-wider text-emerald-900/60">Alamat Tempat Tinggal</dt>
                            <dd class="mt-0.5 text-sm text-emerald-950 leading-relaxed">{{ $student->address ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Detail Akademik & Mata Pelajaran -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Card Kelas Aktif -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Rombel Semester Ini</span>
                            <h2 class="text-lg font-bold text-emerald-950">
                                {{ $activeSemester ? $activeSemester->full_name : 'Semester Belum Diaktifkan' }}
                            </h2>
                        </div>
                        @if ($currentClass)
                            <span class="rounded-full bg-emerald-800 px-3 py-1 text-xs font-bold text-amber-300">
                                Kelas {{ $currentClass->name }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 rounded-2xl border border-emerald-100 bg-[#f5fbef] p-4">
                        @if ($currentClass)
                            <p class="text-xs text-emerald-900/70">
                                Terdaftar pada rombel <strong>Kelas {{ $currentClass->name }} (Tingkat {{ $currentClass->grade_level }})</strong>.
                                Persiapan rapor dan penilaian sedang dikelola oleh dewan guru.
                            </p>
                        @else
                            <p class="text-xs text-emerald-900/60 italic">
                                Belum ada rombongan belajar yang terhubung untuk semester ini.
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Card Rapor Resmi yang Telah Diterbitkan -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Dokumen Hasil Belajar</span>
                            <h2 class="text-lg font-bold text-emerald-950">Buku Rapor Resmi Terbit</h2>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                            {{ $student->reportPublications->count() }} Rapor Tersedia
                        </span>
                    </div>

                    @if ($student->reportPublications->isNotEmpty())
                        <div class="space-y-3">
                            @foreach ($student->reportPublications as $pub)
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="rounded-full bg-emerald-700 px-2 py-0.5 text-[10px] font-bold text-white">
                                                Terbit
                                            </span>
                                            <strong class="text-sm font-bold text-emerald-950">
                                                Semester {{ $pub->semester->name }} ({{ $pub->semester->academicYear->name }})
                                            </strong>
                                        </div>
                                        <p class="text-xs text-emerald-900/60">
                                            Diterbitkan resmi pada {{ $pub->published_at ? $pub->published_at->translatedFormat('d F Y, H:i') : '-' }} WIB
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('wali.reports.show', $pub) }}"
                                           class="rounded-xl bg-emerald-800 px-3.5 py-2 text-xs font-bold text-white hover:bg-emerald-900 transition">
                                            Lihat Rapor
                                        </a>
                                        <a href="{{ route('wali.reports.download', $pub) }}"
                                           class="rounded-xl border border-emerald-300 bg-white px-3.5 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-50 transition">
                                            Unduh PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/40 p-6 text-center text-xs text-emerald-900/60 space-y-1">
                            <p class="font-bold text-emerald-950">Belum ada buku rapor resmi yang diterbitkan.</p>
                            <p>Rapor akan otomatis muncul di sini setelah dewan guru dan kepala madrasah mengesahkan serta menerbitkan rapor.</p>
                        </div>
                    @endif
                </div>

                <!-- Card Mata Pelajaran Semester Aktif -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-bold text-emerald-950">Mata Pelajaran yang Diajarkan</h3>
                    <p class="text-xs text-emerald-900/60 mt-0.5">Daftar mapel pada rombel kelas anak di semester aktif saat ini:</p>

                    <div class="mt-4">
                        @if ($subjects->isNotEmpty())
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach ($subjects as $subj)
                                    <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-[#f5fbef] p-3">
                                        <span class="grid size-8 place-items-center rounded-xl bg-emerald-800 font-mono text-xs font-bold text-white">
                                            {{ $subj->order }}
                                        </span>
                                        <div>
                                            <strong class="block text-xs font-bold text-emerald-950">{{ $subj->name }}</strong>
                                            <span class="font-mono text-[10px] text-emerald-900/60">Kode: {{ $subj->code }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/40 p-4 text-center text-xs text-emerald-900/60">
                                Belum ada mata pelajaran yang ditugaskan ke kelas anak pada semester ini.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Riwayat Penempatan Kelas -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-bold text-emerald-950">Riwayat Penempatan Kelas</h3>
                    <p class="text-xs text-emerald-900/60 mt-0.5">Catatan riwayat rombel kelas anak di setiap semester:</p>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm text-emerald-950">
                            <thead class="border-b border-emerald-100 bg-emerald-50/60 text-xs uppercase font-bold text-emerald-900/70">
                                <tr>
                                    <th class="px-4 py-3">Tahun Ajaran</th>
                                    <th class="px-4 py-3">Semester</th>
                                    <th class="px-4 py-3">Kelas</th>
                                    <th class="px-4 py-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100/60">
                                @forelse ($student->enrollments->sortByDesc('id') as $enr)
                                    <tr class="hover:bg-emerald-50/40">
                                        <td class="px-4 py-3 font-semibold">{{ $enr->semester->academicYear->name ?? '-' }}</td>
                                        <td class="px-4 py-3">Semester {{ $enr->semester->name ?? '-' }}</td>
                                        <td class="px-4 py-3 font-bold text-emerald-900">Kelas {{ $enr->schoolClass->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            @if ($enr->semester?->is_active)
                                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                                    Semester Aktif
                                                </span>
                                            @else
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600">
                                                    Riwayat Lalu
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-emerald-900/50 text-xs">
                                            Belum ada catatan riwayat kelas sebelumnya.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
