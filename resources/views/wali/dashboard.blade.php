@extends('layouts.dashboard', ['isAdmin' => false])

@section('title', 'Portal Wali Murid')

@section('content')
    <div class="mx-auto max-w-5xl space-y-8">
        <!-- Header Banner -->
        <div class="rounded-3xl bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-800 p-7 text-white shadow-xl shadow-emerald-950/15 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 size-48 rounded-full bg-amber-400/10 blur-2xl"></div>
            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-300">
                    <span>✦</span> Portal Resmi Wali Siswa
                </span>
                <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl md:text-4xl text-white">
                    Assalamu’alaikum, {{ auth()->user()->name }}.
                </h1>
                <p class="mt-2 text-sm text-emerald-100/80 max-w-2xl leading-relaxed">
                    Selamat datang di portal informasi akademik madrasah. Pantau data siswa, penempatan rombel kelas, serta persiapan penerbitan rapor putra-putri Anda.
                </p>
            </div>
        </div>

        <!-- Section Anak yang Terhubung -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-emerald-950">Daftar Putra-Putri Anda</h2>
                    <p class="text-xs text-emerald-900/60 mt-0.5">Data siswa yang terhubung secara resmi dengan akun wali Anda</p>
                </div>
                @if ($children->isNotEmpty())
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                        {{ $children->count() }} Anak Terdaftar
                    </span>
                @endif
            </div>

            @if ($children->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2">
                    @foreach ($children as $child)
                        @php
                            $currEnrollment = $child->currentEnrollment($activeSemester);
                            $currClass = $currEnrollment?->schoolClass;
                        @endphp
                        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm hover:shadow-md transition">
                            <div class="flex items-start gap-4">
                                <span class="grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-amber-200 to-amber-300 text-2xl font-black text-emerald-950 shadow-sm">
                                    {{ strtoupper(mb_substr($child->name, 0, 1)) }}
                                </span>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-emerald-950 leading-snug">{{ $child->name }}</h3>
                                    <div class="flex items-center gap-2 text-xs text-emerald-900/60 mt-0.5">
                                        <span>NIS: <strong class="font-mono">{{ $child->nis }}</strong></span>
                                        <span>•</span>
                                        <span>{{ $child->gender }}</span>
                                    </div>
                                    <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                                        Hubungan: {{ $child->pivot->relationship ?? 'Anak' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Info Kelas Semester Aktif -->
                            <div class="mt-5 rounded-2xl border border-emerald-100 bg-[#f5fbef] p-4 space-y-1.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-900/60">
                                    Status Kelas ({{ $activeSemester ? $activeSemester->full_name : 'Semester Ini' }})
                                </span>
                                @if ($currClass)
                                    <div class="flex items-center justify-between">
                                        <strong class="text-base font-bold text-emerald-950">
                                            Kelas {{ $currClass->name }} (Tingkat {{ $currClass->grade_level }})
                                        </strong>
                                        <span class="rounded-full bg-emerald-800 px-2.5 py-0.5 text-[10px] font-bold text-amber-300">
                                            Aktif
                                        </span>
                                    </div>
                                @else
                                    <p class="text-xs text-emerald-900/50 italic">
                                        Belum ditempatkan di kelas untuk semester ini.
                                    </p>
                                @endif
                            </div>

                            <!-- Status Rapor -->
                            @php
                                $latestReport = $child->reportPublications->first();
                            @endphp
                            <div class="mt-3 rounded-2xl border {{ $latestReport ? 'border-emerald-300 bg-emerald-50/70' : 'border-slate-200 bg-slate-50/70' }} p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $latestReport ? 'text-emerald-800' : 'text-slate-600' }}">
                                        Buku Rapor Siswa
                                    </span>
                                    @if ($latestReport)
                                        <span class="rounded-full bg-emerald-700 px-2 py-0.5 text-[10px] font-bold text-white">
                                            Terbit
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                            Belum Terbit
                                        </span>
                                    @endif
                                </div>
                                @if ($latestReport)
                                    <p class="text-xs text-emerald-950 font-medium">
                                        Rapor Semester {{ $latestReport->semester->name }} ({{ $latestReport->semester->academicYear->name }}) telah resmi diterbitkan.
                                    </p>
                                    <div class="flex items-center gap-2 pt-1">
                                        <a href="{{ route('wali.reports.show', $latestReport) }}" class="rounded-lg bg-emerald-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-900 transition">
                                            Buka Rapor
                                        </a>
                                        <a href="{{ route('wali.reports.download', $latestReport) }}" class="rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-bold text-emerald-800 hover:bg-emerald-50 transition">
                                            Unduh PDF
                                        </a>
                                    </div>
                                @else
                                    <p class="text-xs text-slate-500 italic">
                                        Rapor belum diterbitkan oleh pihak madrasah untuk siswa ini.
                                    </p>
                                @endif
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="mt-5 pt-4 border-t border-emerald-100 flex items-center justify-between">
                                <span class="text-xs text-emerald-900/50">
                                    {{ $child->enrollments->count() }} semester riwayat
                                </span>
                                <a href="{{ route('wali.children.show', $child) }}" class="rounded-xl bg-emerald-800 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-900 transition inline-flex items-center gap-1.5">
                                    <span>Lihat Detail Anak</span>
                                    <span>→</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Kondisi Kosong (Empty State) Sesuai Panduan -->
                <div class="rounded-3xl border border-dashed border-emerald-200 bg-white p-10 text-center shadow-sm">
                    <span class="inline-grid size-16 place-items-center rounded-3xl bg-amber-100 text-3xl">⌁</span>
                    <h3 class="mt-4 text-xl font-bold text-emerald-950">Belum ada data anak yang terhubung</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm text-emerald-900/65 leading-relaxed">
                        Data anak Anda akan otomatis muncul di halaman ini setelah administrator madrasah menautkan akun wali Anda dengan data siswa.
                    </p>
                    <div class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-[#f5fbef] border border-emerald-100 px-4 py-2.5 text-xs text-emerald-900">
                        <span>ℹ️</span>
                        <span>Jika Anda memerlukan bantuan, silakan hubungi tata usaha atau admin madrasah.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
