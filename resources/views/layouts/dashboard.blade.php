<div>
    <!-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison -->
</div>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5fbef] text-emerald-950 antialiased">
    <div class="min-h-screen md:grid md:grid-cols-[16rem_1fr]">
        <aside class="hidden bg-emerald-950 p-6 text-white md:flex md:flex-col">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-white no-underline">
                <span class="grid size-10 place-items-center rounded-2xl bg-amber-300 text-lg text-emerald-950">✦</span>
                <span><strong class="block text-lg">{{ config('app.name') }}</strong><small class="text-emerald-100/70">Sistem E-Rapor MI</small></span>
            </a>
            <nav class="mt-12 grid gap-2" aria-label="Menu dashboard">
                <a href="{{ $isAdmin ? route('admin.dashboard') : route('wali.dashboard') }}" class="rounded-xl bg-white/15 px-4 py-3 font-semibold text-white">Ringkasan</a>
                <span class="rounded-xl px-4 py-3 text-sm text-white/45">{{ $isAdmin ? 'Data akademik segera hadir' : 'Rapor anak segera hadir' }}</span>
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-white/20 px-4 py-3 text-left font-semibold transition hover:bg-white/10">Keluar</button>
            </form>
        </aside>

        <div>
            <header class="flex items-center justify-between border-b border-emerald-950/10 bg-white px-5 py-4 md:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold md:hidden"><span class="grid size-9 place-items-center rounded-xl bg-emerald-800 text-white">✦</span>{{ config('app.name') }}</a>
                <p class="hidden text-sm font-semibold text-emerald-900/60 md:block">{{ $isAdmin ? 'Area Administrasi' : 'Portal Wali / Siswa' }}</p>
                <details class="relative md:hidden"><summary class="cursor-pointer list-none rounded-xl bg-emerald-800 px-4 py-2 text-sm font-bold text-white">Menu</summary><div class="absolute right-0 top-12 z-20 w-60 rounded-2xl border border-emerald-950/10 bg-white p-3 shadow-xl"><a href="{{ $isAdmin ? route('admin.dashboard') : route('wali.dashboard') }}" class="block rounded-xl px-3 py-2 font-semibold hover:bg-emerald-50">Ringkasan</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="mt-1 w-full rounded-xl px-3 py-2 text-left font-semibold text-rose-700 hover:bg-rose-50">Keluar</button></form></div></details>
                <div class="hidden items-center gap-3 md:flex"><span class="grid size-10 place-items-center rounded-full bg-amber-100 font-bold text-emerald-800">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><div class="text-sm"><strong class="block">{{ auth()->user()->name }}</strong><span class="text-emerald-900/55">{{ $isAdmin ? 'Admin' : 'Wali / Siswa' }}</span></div></div>
            </header>
            @if (session('status'))
                <div class="mx-5 mt-5 flex items-start justify-between gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900 md:mx-8"><span>{{ session('status') }}</span></div>
            @endif
            <main class="px-5 py-8 md:px-8">@yield('content')</main>
        </div>
    </div>
</body>
</html>
