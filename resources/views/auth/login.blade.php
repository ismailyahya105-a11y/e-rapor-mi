<div>
    <!-- Very little is needed to make a happy life. - Marcus Aurelius -->
</div>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-amber-50 text-emerald-950 antialiased">
    <main class="mx-auto grid min-h-screen max-w-6xl items-center gap-10 px-5 py-10 lg:grid-cols-2 lg:px-10">
        <section class="hidden rounded-[2.5rem] bg-emerald-950 p-12 text-white lg:block"><span class="grid size-14 place-items-center rounded-3xl bg-amber-300 text-2xl text-emerald-950">✦</span><p class="mt-16 text-sm font-bold tracking-[.2em] text-amber-300">SELAMAT DATANG</p><h1 class="mt-4 text-5xl font-black leading-tight">Rapor yang dekat dengan setiap keluarga.</h1><p class="mt-6 max-w-md text-lg leading-8 text-emerald-100/75">Masuk untuk mengelola atau melihat perjalanan belajar di {{ config('app.name') }}.</p></section>
        <section class="mx-auto w-full max-w-md rounded-[2rem] bg-white p-7 shadow-2xl shadow-emerald-950/10 sm:p-10"><a href="{{ route('home') }}" class="flex items-center gap-3 font-black text-emerald-950 no-underline"><span class="grid size-10 place-items-center rounded-2xl bg-emerald-800 text-white">✦</span>{{ config('app.name') }}</a><h2 class="mt-10 text-3xl font-black">Masuk ke akun</h2><p class="mt-2 text-emerald-950/60">Gunakan akun yang dibuat oleh admin madrasah.</p>
            @if (session('status'))<div class="mt-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>@endif
            <form method="POST" action="{{ route('login.store') }}" class="mt-8 grid gap-5">@csrf
                <label class="grid gap-2 text-sm font-bold">Alamat email<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus class="rounded-xl border border-emerald-950/15 px-4 py-3 font-normal outline-none transition focus:border-emerald-700 focus:ring-4 focus:ring-emerald-100">@error('email')<span class="font-normal text-rose-700">{{ $message }}</span>@enderror</label>
                <label class="grid gap-2 text-sm font-bold">Kata sandi<input type="password" name="password" autocomplete="current-password" required class="rounded-xl border border-emerald-950/15 px-4 py-3 font-normal outline-none transition focus:border-emerald-700 focus:ring-4 focus:ring-emerald-100">@error('password')<span class="font-normal text-rose-700">{{ $message }}</span>@enderror</label>
                <button type="submit" class="mt-2 rounded-xl bg-emerald-800 px-5 py-3.5 font-bold text-white shadow-lg shadow-emerald-950/15 transition hover:bg-emerald-950">Masuk ke RAPORTKU →</button>
            </form>
            <p class="mt-6 text-center text-sm text-emerald-950/55">Belum memiliki akun? Hubungi admin madrasah.</p>
        </section>
    </main>
</body>
</html>
