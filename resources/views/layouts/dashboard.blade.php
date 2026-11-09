<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5fbef] text-emerald-950 antialiased font-sans">
    <div class="min-h-screen md:grid md:grid-cols-[17rem_1fr]">
        <!-- Sidebar Desktop -->
        <aside class="hidden bg-emerald-950 p-6 text-white md:flex md:flex-col border-r border-emerald-900/40">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-white no-underline">
                <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-amber-300 to-amber-400 text-lg font-bold text-emerald-950 shadow-md shadow-amber-950/20">✦</span>
                <span>
                    <strong class="block text-lg tracking-tight">{{ config('app.name') }}</strong>
                    <small class="text-xs font-medium text-emerald-300/80">Sistem E-Rapor MI</small>
                </span>
            </a>

            <nav class="mt-8 flex-1 space-y-1.5 overflow-y-auto pr-1" aria-label="Menu dashboard">
                @if ($isAdmin)
                    <p class="px-3 pt-2 text-[11px] font-bold uppercase tracking-wider text-emerald-400/60">Utama</p>
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">📊</span>
                        <span>Ringkasan</span>
                    </a>

                    <p class="px-3 pt-4 text-[11px] font-bold uppercase tracking-wider text-emerald-400/60">Penilaian Akademik</p>
                    <a href="{{ route('admin.assessments.components.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.assessments.components.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">⚙️</span>
                        <span>Pengaturan Penilaian</span>
                    </a>

                    <a href="{{ route('admin.assessments.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.assessments.index') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">📝</span>
                        <span>Input Nilai Akademik</span>
                    </a>

                    <a href="{{ route('admin.assessments.recap') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.assessments.recap') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">📈</span>
                        <span>Rekap Nilai Akademik</span>
                    </a>

                    <a href="{{ route('admin.leger.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.leger.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">📑</span>
                        <span>Leger Nilai Kelas</span>
                    </a>

                    <p class="px-3 pt-4 text-[11px] font-bold uppercase tracking-wider text-emerald-400/60">Penilaian Nonakademik</p>
                    <a href="{{ route('admin.attitudes.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.attitudes.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">⭐</span>
                        <span>Sikap & Perilaku</span>
                    </a>

                    <a href="{{ route('admin.attendances.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.attendances.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">📅</span>
                        <span>Kehadiran Siswa</span>
                    </a>

                    <a href="{{ route('admin.activities.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.activities.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">💡</span>
                        <span>Keaktifan Belajar</span>
                    </a>

                    <a href="{{ route('admin.extracurriculars.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.extracurriculars.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">⛺</span>
                        <span>Ekstrakurikuler</span>
                    </a>

                    <a href="{{ route('admin.achievements.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.achievements.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">🏆</span>
                        <span>Prestasi Siswa</span>
                    </a>

                    <a href="{{ route('admin.homeroom-notes.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.homeroom-notes.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">✍️</span>
                        <span>Catatan Wali Kelas</span>
                    </a>

                    <a href="{{ route('admin.non-academic-recap.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.non-academic-recap.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">📋</span>
                        <span>Rekap Nonakademik</span>
                    </a>

                    <p class="px-3 pt-4 text-[11px] font-bold uppercase tracking-wider text-emerald-400/60">Penerbitan Rapor</p>
                    <a href="{{ route('admin.report-drafts.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.report-drafts.*') || request()->routeIs('admin.report-publications.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">📑</span>
                        <span>Pengelolaan Rapor</span>
                    </a>

                    <p class="px-3 pt-4 text-[11px] font-bold uppercase tracking-wider text-emerald-400/60">Data Master</p>
                    <a href="{{ route('admin.academic-years.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.academic-years.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">🗓️</span>
                        <span>Tahun & Semester</span>
                    </a>

                    <a href="{{ route('admin.school-classes.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.school-classes.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">🏫</span>
                        <span>Kelas</span>
                    </a>

                    <a href="{{ route('admin.students.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.students.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">🎒</span>
                        <span>Data Siswa</span>
                    </a>

                    <a href="{{ route('admin.enrollments.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.enrollments.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">👥</span>
                        <span>Penempatan Kelas</span>
                    </a>

                    <a href="{{ route('admin.school-profile.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.school-profile.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">⚙</span>
                        <span>Profil Madrasah</span>
                    </a>

                    <p class="px-3 pt-4 text-[11px] font-bold uppercase tracking-wider text-emerald-400/60">Pembelajaran</p>
                    <a href="{{ route('admin.subjects.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.subjects.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">📚</span>
                        <span>Mata Pelajaran</span>
                    </a>

                    <a href="{{ route('admin.subject-assignments.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.subject-assignments.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">📋</span>
                        <span>Penugasan Mapel</span>
                    </a>

                    <p class="px-3 pt-4 text-[11px] font-bold uppercase tracking-wider text-emerald-400/60">Pengguna & Wali</p>
                    <a href="{{ route('admin.guardians.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.guardians.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">🤝</span>
                        <span>Akun Wali & Anak</span>
                    </a>

                    <p class="px-3 pt-4 text-[11px] font-bold uppercase tracking-wider text-emerald-400/60">Pemeliharaan</p>
                    <a href="{{ route('admin.backup.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.backup.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">💾</span>
                        <span>Pencadangan Sistem</span>
                    </a>

                    <a href="{{ route('admin.system.status') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.system.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">🛡️</span>
                        <span>Status Sistem</span>
                    </a>
                @else
                    <a href="{{ route('wali.dashboard') }}"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('wali.dashboard') || request()->routeIs('wali.children.*') ? 'bg-emerald-800 text-white shadow-sm border-l-4 border-amber-300' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-base">👨‍👩‍👧</span>
                        <span>Data Anak Saya</span>
                    </a>
                @endif
            </nav>

            <div class="pt-4 border-t border-emerald-900/50">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl border border-white/15 px-4 py-2.5 text-left text-sm font-semibold text-emerald-200 transition hover:bg-white/10 hover:text-white">
                        <span>↪</span>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Body -->
        <div class="flex flex-col min-h-screen">
            <!-- Header Topbar -->
            <header class="sticky top-0 z-20 flex items-center justify-between border-b border-emerald-950/10 bg-white/95 backdrop-blur px-5 py-3.5 md:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold md:hidden text-emerald-950 no-underline">
                    <span class="grid size-9 place-items-center rounded-xl bg-emerald-800 text-white text-base">✦</span>
                    <span class="text-lg tracking-tight">{{ config('app.name') }}</span>
                </a>

                <div class="hidden items-center gap-2 text-sm font-semibold text-emerald-900/70 md:flex">
                    <span class="inline-flex items-center rounded-md bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">
                        {{ $isAdmin ? 'ADMINISTRATOR' : 'WALI SISWA' }}
                    </span>
                    <span>Madrasah Ibtidaiyah</span>
                </div>

                <!-- Mobile Menu Drawer -->
                <details class="relative md:hidden">
                    <summary class="cursor-pointer list-none rounded-xl bg-emerald-800 px-3.5 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-900">
                        ☰ Menu
                    </summary>
                    <div class="absolute right-0 top-12 z-30 w-64 rounded-2xl border border-emerald-950/10 bg-white p-3 shadow-2xl space-y-1">
                        @if ($isAdmin)
                            <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-emerald-50">Ringkasan</a>
                            <p class="px-3 pt-2 text-[10px] font-bold uppercase tracking-wider text-emerald-700/60">Penilaian Akademik</p>
                            <a href="{{ route('admin.assessments.components.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Pengaturan Penilaian</a>
                            <a href="{{ route('admin.assessments.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Input Nilai Akademik</a>
                            <a href="{{ route('admin.assessments.recap') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Rekap Nilai Akademik</a>
                            <a href="{{ route('admin.leger.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Leger Nilai Kelas</a>
                            <p class="px-3 pt-2 text-[10px] font-bold uppercase tracking-wider text-emerald-700/60">Penilaian Nonakademik</p>
                            <a href="{{ route('admin.attitudes.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Sikap & Perilaku</a>
                            <a href="{{ route('admin.attendances.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Kehadiran Siswa</a>
                            <a href="{{ route('admin.activities.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Keaktifan Belajar</a>
                            <a href="{{ route('admin.extracurriculars.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Ekstrakurikuler</a>
                            <a href="{{ route('admin.achievements.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Prestasi Siswa</a>
                            <a href="{{ route('admin.homeroom-notes.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Catatan Wali Kelas</a>
                            <a href="{{ route('admin.non-academic-recap.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Rekap Nonakademik</a>
                            <p class="px-3 pt-2 text-[10px] font-bold uppercase tracking-wider text-emerald-700/60">Penerbitan Rapor</p>
                            <a href="{{ route('admin.report-drafts.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Pengelolaan Rapor</a>
                            <p class="px-3 pt-2 text-[10px] font-bold uppercase tracking-wider text-emerald-700/60">Data Master</p>
                            <a href="{{ route('admin.academic-years.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-emerald-50">Tahun & Semester</a>
                            <a href="{{ route('admin.school-classes.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-emerald-50">Kelas</a>
                            <a href="{{ route('admin.students.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-emerald-50">Data Siswa</a>
                            <a href="{{ route('admin.enrollments.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-emerald-50">Penempatan Kelas</a>
                            <a href="{{ route('admin.subjects.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-emerald-50">Mata Pelajaran</a>
                            <a href="{{ route('admin.subject-assignments.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-emerald-50">Penugasan Mapel</a>
                            <a href="{{ route('admin.guardians.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-emerald-50">Akun Wali & Anak</a>
                            <p class="px-3 pt-2 text-[10px] font-bold uppercase tracking-wider text-emerald-700/60">Pemeliharaan</p>
                            <a href="{{ route('admin.backup.index') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Pencadangan Sistem</a>
                            <a href="{{ route('admin.system.status') }}" class="block rounded-xl px-3 py-1.5 text-xs font-semibold hover:bg-emerald-50">Status Sistem</a>
                        @else
                            <a href="{{ route('wali.dashboard') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold hover:bg-emerald-50">Data Anak Saya</a>
                        @endif
                        <hr class="my-2 border-emerald-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-sm font-semibold text-rose-700 hover:bg-rose-50">Keluar</button>
                        </form>
                    </div>
                </details>

                <!-- Profile Info Desktop -->
                <div class="hidden items-center gap-3 md:flex">
                    <span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-amber-100 to-amber-200 font-black text-emerald-800 shadow-sm">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <div class="text-sm text-right">
                        <strong class="block leading-snug">{{ auth()->user()->name }}</strong>
                        <span class="text-xs text-emerald-900/60">{{ auth()->user()->email }}</span>
                    </div>
                </div>
            </header>

            <!-- Flash Alerts -->
            @if (session('status') || session('success'))
                <div class="mx-5 mt-5 flex items-start gap-3 rounded-2xl border border-emerald-300 bg-emerald-50/90 p-4 text-sm text-emerald-950 md:mx-8 shadow-sm">
                    <span class="text-emerald-700 text-lg">✓</span>
                    <span class="font-medium">{{ session('status') ?? session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mx-5 mt-5 flex items-start gap-3 rounded-2xl border border-rose-300 bg-rose-50/90 p-4 text-sm text-rose-950 md:mx-8 shadow-sm">
                    <span class="text-rose-700 text-lg">⚠️</span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Main Content Area -->
            <main class="flex-1 px-5 py-6 md:px-8 md:py-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="border-t border-emerald-950/10 bg-white/50 px-5 py-4 text-center text-xs text-emerald-900/50 md:px-8">
                RAPORTKU &copy; {{ date('Y') }} — Sistem Pengelolaan Data dan Penilaian Akademik Madrasah Ibtidaiyah
            </footer>
        </div>
    </div>
</body>
</html>
