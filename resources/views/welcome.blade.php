<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="RAPORTKU, Sistem E-Rapor Madrasah Ibtidaiyah.">
    <title>{{ config('app.name') }} — Sistem E-Rapor Madrasah Ibtidaiyah</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #123c37; background: #fcfffb; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        .font-display { font-family: Georgia, Cambria, "Times New Roman", serif; }
        .dot-pattern { background-image: radial-gradient(#0b8a6930 1px, transparent 1px); background-size: 15px 15px; }
        .header-actions { margin-left: auto; }
        @media (min-width: 768px) {
            .header-actions { display: flex !important; align-items: center; gap: 1.75rem; }
            .header-actions nav { display: flex !important; }
        }
    </style>
</head>
<body class="antialiased">
    <header class="border-b border-emerald-950/10 bg-white/90 px-5 py-4 backdrop-blur md:px-10">
        <div class="mx-auto flex max-w-6xl items-center gap-5">
            <a href="#beranda" class="flex items-center gap-3 no-underline">
                <span class="grid size-11 place-items-center rounded-2xl bg-emerald-700 text-xl text-white shadow-lg shadow-emerald-950/15">✦</span>
                <span class="leading-tight"><strong class="block text-lg tracking-tight">{{ config('app.name') }}</strong><small class="text-emerald-900/65">Sistem E-Rapor MI</small></span>
            </a>
            <div class="header-actions hidden items-center gap-7 md:flex">
            <nav class="items-center gap-7 text-sm font-semibold md:flex" aria-label="Navigasi utama">
                <a href="#tentang" class="transition hover:text-emerald-700">Tentang</a>
                <a href="#fitur" class="transition hover:text-emerald-700">Fitur</a>
                <a href="#panduan" class="transition hover:text-emerald-700">Panduan</a>
            </nav>
            <a href="{{ route('login') }}" class="rounded-full bg-emerald-800 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-950/15 transition hover:bg-emerald-950">Masuk Portal →</a>
            </div>
        </div>
    </header>

    <main id="beranda">
        <section class="relative overflow-hidden bg-gradient-to-b from-sky-50 via-emerald-50 to-[#dff4d4] px-5 pt-16 md:px-10 md:pt-24">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-64 dot-pattern opacity-55"></div>
            <div class="relative mx-auto grid max-w-6xl items-center gap-12 pb-28 lg:grid-cols-[1.05fr_.95fr]">
                <div>
                    <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-emerald-800 shadow-sm"><span class="size-2 rounded-full bg-amber-400"></span> Ruang belajar yang lebih terhubung</p>
                    <h1 class="font-display max-w-xl text-5xl font-bold leading-[1.03] tracking-tight text-emerald-950 sm:text-6xl">Rapor yang jelas,<br><span class="text-emerald-700">untuk tumbuh bersama.</span></h1>
                    <p class="mt-6 max-w-lg text-lg leading-8 text-emerald-950/75">{{ config('app.name') }} membantu madrasah, wali, dan siswa mengikuti hasil belajar semester dengan sederhana, aman, dan penuh perhatian.</p>
                    <div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('login') }}" class="rounded-full bg-amber-400 px-6 py-3 font-bold text-emerald-950 shadow-lg shadow-amber-600/20 transition hover:bg-amber-300">Lihat portal rapor</a><a href="#fitur" class="rounded-full border border-emerald-900/20 bg-white/80 px-6 py-3 font-bold text-emerald-900 transition hover:bg-white">Kenali fiturnya</a></div>
                </div>
                <div class="relative mx-auto w-full max-w-md">
                    <div class="absolute -inset-7 rounded-[3rem] bg-amber-200/45 blur-2xl"></div>
                    <div class="relative overflow-hidden rounded-[2.5rem] border-8 border-white bg-[#ffefbd] p-7 shadow-2xl shadow-emerald-950/15">
                        <div class="flex items-center justify-between text-emerald-950"><span class="rounded-full bg-white px-3 py-1 text-xs font-bold">SEMESTER GANJIL</span><span class="text-lg">☀</span></div>
                        <div class="mt-7 rounded-[2rem] bg-emerald-800 p-6 text-white"><p class="text-sm font-medium text-emerald-100">Selamat datang di</p><p class="mt-1 text-3xl font-black tracking-tight">RAPORTKU</p><div class="mt-6 grid grid-cols-3 gap-2 text-center text-xs font-semibold"><span class="rounded-xl bg-white/15 p-3">Nilai</span><span class="rounded-xl bg-white/15 p-3">Absensi</span><span class="rounded-xl bg-white/15 p-3">Pesan</span></div></div>
                        <div class="mt-6 flex items-end justify-between rounded-3xl bg-white p-5 shadow-sm"><div><p class="text-xs font-bold text-emerald-700">Perjalanan belajarku</p><p class="mt-1 text-xl font-black">Di MI Islamiyah Syafi'iyah</p></div><svg class="size-20 text-emerald-500" viewBox="0 0 100 100" fill="none"><path d="M10 80C28 58 34 55 48 67C62 31 77 35 90 17" stroke="currentColor" stroke-width="9" stroke-linecap="round"/><circle cx="90" cy="17" r="7" fill="#f3bd42"/></svg></div>
                    </div>
                </div>
            </div>
            <div class="absolute -bottom-1 left-0 h-16 w-full bg-[#fcfffb] [clip-path:ellipse(70%_55%_at_50%_100%)]"></div>
        </section>

        <section id="tentang" class="px-5 py-16 md:px-10"><div class="mx-auto grid max-w-6xl gap-7 md:grid-cols-3">
            <article class="rounded-3xl bg-emerald-800 p-7 text-white shadow-xl shadow-emerald-950/10"><span class="text-3xl">☷</span><h2 class="mt-8 text-xl font-bold">Rapor terpusat</h2><p class="mt-2 leading-7 text-emerald-50/80">Satu tempat yang rapi untuk catatan hasil belajar dan kehadiran semester.</p></article>
            <article class="rounded-3xl bg-amber-300 p-7 shadow-xl shadow-amber-950/10"><span class="text-3xl">✎</span><h2 class="mt-8 text-xl font-bold">Komunikasi dua arah</h2><p class="mt-2 leading-7 text-emerald-950/75">Wali dapat membaca, memberi tanggapan, dan mengikuti pembaruan dari madrasah.</p></article>
            <article class="rounded-3xl bg-[#cbeac0] p-7 shadow-xl shadow-emerald-950/10"><span class="text-3xl">⌁</span><h2 class="mt-8 text-xl font-bold">Aman untuk keluarga</h2><p class="mt-2 leading-7 text-emerald-950/75">Setiap keluarga hanya melihat data anak yang memang terhubung dengan akunnya.</p></article>
        </div></section>

        <section id="fitur" class="bg-emerald-950 px-5 py-20 text-white md:px-10"><div class="mx-auto max-w-6xl"><div class="max-w-2xl"><p class="font-bold tracking-[.18em] text-amber-300">UNTUK MADRASAH &amp; KELUARGA</p><h2 class="font-display mt-3 text-4xl font-bold leading-tight md:text-5xl">Semua yang penting, hadir dalam satu perjalanan.</h2></div><div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-4"><div class="rounded-3xl border border-white/15 bg-white/10 p-6"><b>01</b><h3 class="mt-7 text-xl font-bold">Data siswa</h3><p class="mt-2 text-sm leading-6 text-white/70">Profil dan kelas tertata dari awal.</p></div><div class="rounded-3xl border border-white/15 bg-white/10 p-6"><b>02</b><h3 class="mt-7 text-xl font-bold">Nilai semester</h3><p class="mt-2 text-sm leading-6 text-white/70">Rekap capaian yang mudah dibaca.</p></div><div class="rounded-3xl border border-white/15 bg-white/10 p-6"><b>03</b><h3 class="mt-7 text-xl font-bold">Rapor digital</h3><p class="mt-2 text-sm leading-6 text-white/70">Riwayat rapor tersimpan dengan aman.</p></div><div class="rounded-3xl border border-white/15 bg-white/10 p-6"><b>04</b><h3 class="mt-7 text-xl font-bold">Tanggapan wali</h3><p class="mt-2 text-sm leading-6 text-white/70">Komunikasi lebih dekat dan terarah.</p></div></div></div></section>

        <section id="portal" class="bg-[#f5fbef] px-5 py-20 md:px-10"><div class="mx-auto grid max-w-6xl items-center gap-10 rounded-[2.5rem] bg-white p-8 shadow-xl shadow-emerald-950/8 md:grid-cols-[1.2fr_.8fr] md:p-12"><div><p class="font-bold tracking-[.18em] text-emerald-700">MULAI DARI SINI</p><h2 class="font-display mt-3 text-4xl font-bold text-emerald-950">Portal sesuai peran Anda.</h2><p class="mt-4 max-w-lg leading-7 text-emerald-950/70">Masuklah menggunakan akun yang dibuat oleh admin madrasah. Halaman login dan fitur akademik akan menjadi tahap berikutnya dari pengembangan ini.</p></div><div class="grid gap-3"><a href="{{ route('login') }}" class="rounded-2xl bg-emerald-800 p-5 font-bold text-white transition hover:bg-emerald-950">Saya Admin <span class="float-right">→</span></a><a href="{{ route('login') }}" class="rounded-2xl bg-amber-300 p-5 font-bold text-emerald-950 transition hover:bg-amber-200">Saya Wali / Siswa <span class="float-right">→</span></a></div></div></section>
        <section id="panduan" class="px-5 py-16 text-center md:px-10"><p class="text-sm font-bold tracking-[.15em] text-emerald-700">PANDUAN SINGKAT</p><h2 class="font-display mt-3 text-3xl font-bold">Bersiap untuk semester yang lebih tertata.</h2><p class="mx-auto mt-4 max-w-xl leading-7 text-emerald-950/65">Tampilan awal ini menjadi arah desain untuk portal admin dan wali: hangat, jelas, serta nyaman digunakan di ponsel maupun komputer.</p></section>
    </main>
    <footer class="bg-emerald-950 px-5 py-9 text-center text-sm text-emerald-50/75"><strong class="text-white">{{ config('app.name') }}</strong><span class="mx-2 text-amber-300">✦</span>Sistem E-Rapor Madrasah Ibtidaiyah</footer>
</body>
</html>
