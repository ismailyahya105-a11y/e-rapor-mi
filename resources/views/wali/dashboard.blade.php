<div>
    <!-- Breathing in, I calm body and mind. Breathing out, I smile. - Thich Nhat Hanh -->
</div>
@extends('layouts.dashboard', ['isAdmin' => false])

@section('title', 'Portal Wali')

@section('content')
    <div class="mx-auto max-w-5xl">
        <p class="font-bold tracking-[.18em] text-emerald-700">PORTAL WALI / SISWA</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight md:text-4xl">Halo, {{ auth()->user()->name }}.</h1>
        <p class="mt-3 max-w-2xl leading-7 text-emerald-950/65">Di sini Anda akan dapat melihat rapor anak, mengonfirmasi pembacaan, dan mengirim tanggapan kepada madrasah.</p>

        <section class="mt-8 rounded-[2rem] bg-white p-8 text-center shadow-lg shadow-emerald-950/8 md:p-12"><span class="inline-grid size-16 place-items-center rounded-3xl bg-amber-100 text-3xl">⌁</span><h2 class="mt-5 text-2xl font-bold">Belum ada data anak yang terhubung</h2><p class="mx-auto mt-3 max-w-xl leading-7 text-emerald-950/65">Data anak akan muncul setelah admin madrasah menghubungkan akun Anda. Jika diperlukan, silakan hubungi admin madrasah.</p></section>
    </div>
@endsection
