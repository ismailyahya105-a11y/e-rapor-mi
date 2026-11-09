@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Dashboard Admin')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header Banner -->
        <div class="rounded-3xl bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-800 p-7 text-white shadow-xl shadow-emerald-950/15 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 size-48 rounded-full bg-amber-400/10 blur-2xl"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-300">
                        <span>✦</span> Modul Data Utama & Hubungan Wali–Siswa Aktif
                    </span>
                    <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl md:text-4xl text-white">
                        Selamat datang, {{ auth()->user()->name }}.
                    </h1>
                    <p class="mt-2 text-sm text-emerald-100/80 max-w-2xl leading-relaxed">
                        Kelola data tahun ajaran, semester aktif, rombongan belajar kelas, siswa, penempatan kelas, mata pelajaran, serta penautan akun wali murid secara terpadu.
                    </p>
                </div>

                <!-- Active Period Badge Card -->
                <div class="rounded-2xl bg-white/10 backdrop-blur border border-white/15 p-4 md:min-w-[240px]">
                    <p class="text-xs font-semibold text-emerald-200">Periode Akademik Aktif</p>
                    @if ($activeSemester)
                        <div class="mt-1 flex items-center gap-2">
                            <span class="inline-block size-2.5 rounded-full bg-amber-300 animate-pulse"></span>
                            <strong class="text-base font-bold text-white">{{ $activeSemester->full_name }}</strong>
                        </div>
                        <p class="mt-1 text-xs text-emerald-200/70">
                            {{ $activeEnrollmentCount }} siswa terdaftar di semester ini
                        </p>
                    @else
                        <p class="mt-1 text-sm font-semibold text-amber-300">Belum ada semester aktif</p>
                        <a href="{{ route('admin.academic-years.index') }}" class="mt-2 inline-block text-xs font-bold text-white underline">
                            Atur semester sekarang →
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Metric Cards Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Siswa -->
            <div class="rounded-2xl border border-emerald-950/10 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-900/60">Total Siswa</span>
                    <span class="grid size-9 place-items-center rounded-xl bg-emerald-50 text-emerald-700">🎒</span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-950">{{ $activeStudentsCount }}</span>
                    <span class="text-xs text-emerald-800/60 font-medium">aktif / {{ $studentsCount }} total</span>
                </div>
                <a href="{{ route('admin.students.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:text-emerald-900">
                    Kelola data siswa →
                </a>
            </div>

            <!-- Kelas -->
            <div class="rounded-2xl border border-emerald-950/10 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-900/60">Rombel Kelas</span>
                    <span class="grid size-9 place-items-center rounded-xl bg-amber-50 text-amber-700">🏫</span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-950">{{ $classesCount }}</span>
                    <span class="text-xs text-emerald-800/60 font-medium">kelas terdaftar</span>
                </div>
                <a href="{{ route('admin.school-classes.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:text-emerald-900">
                    Kelola kelas tingkat 1–6 →
                </a>
            </div>

            <!-- Mata Pelajaran -->
            <div class="rounded-2xl border border-emerald-950/10 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-900/60">Mata Pelajaran</span>
                    <span class="grid size-9 place-items-center rounded-xl bg-emerald-50 text-emerald-700">📚</span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-950">{{ $activeSubjectsCount }}</span>
                    <span class="text-xs text-emerald-800/60 font-medium">mapel aktif</span>
                </div>
                <a href="{{ route('admin.subjects.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:text-emerald-900">
                    Mata pelajaran & penugasan →
                </a>
            </div>

            <!-- Akun Wali -->
            <div class="rounded-2xl border border-emerald-950/10 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-900/60">Akun Wali Murid</span>
                    <span class="grid size-9 place-items-center rounded-xl bg-amber-50 text-amber-700">🤝</span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-950">{{ $waliCount }}</span>
                    <span class="text-xs text-emerald-800/60 font-medium">wali terdaftar</span>
                </div>
                <a href="{{ route('admin.guardians.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:text-emerald-900">
                    Tautkan wali dengan anak →
                </a>
            </div>
        </div>

        <!-- Quick Access Shortcuts Grid -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-emerald-950">Aksi Cepat Data Master</h2>
            <p class="mt-1 text-xs text-emerald-900/60">Akses langsung menu pengelolaan data utama untuk tahun ajaran aktif:</p>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('admin.enrollments.index') }}" class="group flex items-start gap-4 rounded-2xl border border-emerald-100 bg-[#f5fbef] p-4 transition hover:border-emerald-300 hover:bg-emerald-50">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-800 text-lg text-white shadow-sm group-hover:scale-105 transition-transform">👥</span>
                    <div>
                        <strong class="block text-sm font-bold text-emerald-950 group-hover:text-emerald-800">Penempatan Kelas Siswa</strong>
                        <p class="mt-1 text-xs text-emerald-900/70">Tempatkan siswa ke kelas pada semester aktif dan jaga konsistensi riwayat.</p>
                    </div>
                </a>

                <a href="{{ route('admin.subject-assignments.index') }}" class="group flex items-start gap-4 rounded-2xl border border-emerald-100 bg-[#f5fbef] p-4 transition hover:border-emerald-300 hover:bg-emerald-50">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-amber-400 text-lg text-emerald-950 shadow-sm group-hover:scale-105 transition-transform">📋</span>
                    <div>
                        <strong class="block text-sm font-bold text-emerald-950 group-hover:text-emerald-800">Penugasan Mapel ke Kelas</strong>
                        <p class="mt-1 text-xs text-emerald-900/70">Atur mata pelajaran yang diajarkan pada masing-masing rombel kelas.</p>
                    </div>
                </a>

                <a href="{{ route('admin.guardians.index') }}" class="group flex items-start gap-4 rounded-2xl border border-emerald-100 bg-[#f5fbef] p-4 transition hover:border-emerald-300 hover:bg-emerald-50">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-900 text-lg text-amber-300 shadow-sm group-hover:scale-105 transition-transform">🔑</span>
                    <div>
                        <strong class="block text-sm font-bold text-emerald-950 group-hover:text-emerald-800">Tautkan Akun Wali & Anak</strong>
                        <p class="mt-1 text-xs text-emerald-900/70">Buat login wali siswa atau hubungkan akun wali ke anak yang bersekolah.</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Students List -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-emerald-950">Siswa Baru Terdaftar</h2>
                    <p class="text-xs text-emerald-900/60">Daftar siswa yang baru saja ditambahkan ke database madrasah</p>
                </div>
                <a href="{{ route('admin.students.create') }}" class="rounded-xl bg-emerald-800 px-4 py-2 text-xs font-bold text-white shadow hover:bg-emerald-900">
                    + Tambah Siswa Baru
                </a>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm text-emerald-950">
                    <thead class="border-b border-emerald-100 bg-emerald-50/60 text-xs uppercase font-bold text-emerald-900/70">
                        <tr>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">Jenis Kelamin</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-100/60">
                        @forelse ($recentStudents as $student)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3 font-mono font-medium">{{ $student->nis }}</td>
                                <td class="px-4 py-3 font-semibold">{{ $student->name }}</td>
                                <td class="px-4 py-3">{{ $student->gender }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $student->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.students.show', $student) }}" class="font-bold text-emerald-700 hover:text-emerald-900">
                                        Detail →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-emerald-900/50">
                                    Belum ada data siswa terdaftar. Silakan tambah data siswa pertama.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
