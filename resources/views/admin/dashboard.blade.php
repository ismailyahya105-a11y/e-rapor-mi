<div>
    <!-- It is not the man who has too little, but the man who craves more, that is poor. - Seneca -->
</div>
@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Dashboard Admin')

@section('content')
    <div class="mx-auto max-w-6xl">
        <p class="font-bold tracking-[.18em] text-emerald-700">DASHBOARD ADMIN</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight md:text-4xl">Selamat datang, {{ auth()->user()->name }}.</h1>
        <p class="mt-3 max-w-2xl leading-7 text-emerald-950/65">Mulai kelola data RAPORTKU dari satu tempat. Modul data madrasah, siswa, dan nilai akan ditambahkan pada tahap berikutnya.</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="rounded-3xl bg-emerald-800 p-6 text-white shadow-lg shadow-emerald-950/10"><p class="text-sm text-emerald-100">Total pengguna</p><p class="mt-3 text-4xl font-black">{{ $userCount }}</p></article>
            <article class="rounded-3xl bg-amber-300 p-6 text-emerald-950 shadow-lg shadow-amber-950/10"><p class="text-sm font-semibold text-emerald-950/70">Admin</p><p class="mt-3 text-4xl font-black">{{ $adminCount }}</p></article>
            <article class="rounded-3xl bg-white p-6 text-emerald-950 shadow-lg shadow-emerald-950/8"><p class="text-sm font-semibold text-emerald-950/60">Akun wali</p><p class="mt-3 text-4xl font-black">{{ $waliCount }}</p></article>
        </div>

        <section class="mt-8 rounded-3xl border border-dashed border-emerald-900/20 bg-white p-8 text-center"><span class="text-4xl">✦</span><h2 class="mt-4 text-xl font-bold">Data akademik belum tersedia</h2><p class="mx-auto mt-2 max-w-lg leading-7 text-emerald-950/65">Tambahkan data profil madrasah, siswa, kelas, dan periode pada modul berikutnya. Ringkasan di atas menggunakan jumlah akun nyata yang tersimpan di database.</p></section>
    </div>
@endsection
