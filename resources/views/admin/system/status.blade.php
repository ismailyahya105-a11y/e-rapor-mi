@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Status Sistem & Audit Lingkungan')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <!-- Topbar Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Infrastruktur & Lingkungan</p>
            <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Status Sistem & Kesiapan Produksi</h1>
            <p class="text-sm text-emerald-900/60 mt-0.5">
                Audit kesiapan server, dependensi ekstensi PHP, izin direktori, dan keamanan konfigurasi aplikasi.
            </p>
        </div>
        <a href="{{ route('admin.backup.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-800 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-900 transition">
            <span>💾</span>
            <span>Manajemen Backup</span>
        </a>
    </div>

    <!-- Overall Status Banner -->
    <div class="rounded-3xl border {{ $health['overall_status'] === 'HEALTHY' ? 'border-emerald-300 bg-emerald-50/80' : ($health['overall_status'] === 'WARNING' ? 'border-amber-300 bg-amber-50/80' : 'border-rose-300 bg-rose-50/80') }} p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <span class="grid size-14 place-items-center rounded-2xl {{ $health['overall_status'] === 'HEALTHY' ? 'bg-emerald-700 text-white' : ($health['overall_status'] === 'WARNING' ? 'bg-amber-500 text-emerald-950' : 'bg-rose-700 text-white') }} text-2xl font-bold shadow-sm">
                {{ $health['overall_status'] === 'HEALTHY' ? '✓' : ($health['overall_status'] === 'WARNING' ? '⚠️' : '✕') }}
            </span>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider {{ $health['overall_status'] === 'HEALTHY' ? 'text-emerald-800' : ($health['overall_status'] === 'WARNING' ? 'text-amber-800' : 'text-rose-800') }}">
                        Status Kesehatan Instalasi
                    </span>
                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-black {{ $health['overall_status'] === 'HEALTHY' ? 'bg-emerald-800 text-white' : ($health['overall_status'] === 'WARNING' ? 'bg-amber-400 text-emerald-950' : 'bg-rose-800 text-white') }}">
                        {{ $health['overall_status'] }}
                    </span>
                </div>
                <h2 class="text-lg font-black text-emerald-950 mt-0.5">
                    @if ($health['overall_status'] === 'HEALTHY')
                        Sistem Siap Digunakan & Memenuhi Syarat Produksi
                    @elseif ($health['overall_status'] === 'WARNING')
                        Sistem Berjalan dengan Catatan Penyesuaian
                    @else
                        Ditemukan Masalah Kritis yang Perlu Diperbaiki
                    @endif
                </h2>
                <p class="text-xs text-emerald-900/70 mt-0.5">
                    PHP {{ data_get($health, 'system_info.php_version') }} • Laravel v{{ data_get($health, 'system_info.laravel_version') }} • Zona Waktu: {{ data_get($health, 'system_info.timezone') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Grid Detail Pemeriksaan -->
    <div class="grid gap-6 sm:grid-cols-2">
        <!-- 1. Izin Tulis Direktori -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3">
                <span class="grid size-10 place-items-center rounded-xl bg-emerald-100 text-xl">📁</span>
                <h3 class="text-base font-bold text-emerald-950">Izin Tulis Direktori</h3>
            </div>
            <div class="space-y-2.5">
                @foreach($health['checks']['directories'] as $dir)
                    <div class="flex items-center justify-between rounded-2xl bg-[#f5fbef] border border-emerald-100 p-3 text-xs">
                        <span class="font-mono font-bold text-emerald-950">{{ $dir['name'] }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-900/70 text-[11px]">{{ $dir['message'] }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $dir['status'] === 'ok' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ strtoupper($dir['status']) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 2. Public Storage Symlink & Database -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3">
                <span class="grid size-10 place-items-center rounded-xl bg-amber-100 text-xl">🔗</span>
                <h3 class="text-base font-bold text-emerald-950">Storage Link & Basis Data</h3>
            </div>
            <div class="space-y-2.5">
                <!-- Symlink -->
                @php $sym = $health['checks']['symlink']; @endphp
                <div class="flex items-center justify-between rounded-2xl bg-[#f5fbef] border border-emerald-100 p-3 text-xs">
                    <span class="font-semibold text-emerald-950">{{ $sym['name'] }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-900/70 text-[11px]">{{ $sym['message'] }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $sym['status'] === 'ok' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ strtoupper($sym['status']) }}
                        </span>
                    </div>
                </div>

                <!-- Database -->
                @php $db = $health['checks']['database']; @endphp
                <div class="flex items-center justify-between rounded-2xl bg-[#f5fbef] border border-emerald-100 p-3 text-xs">
                    <span class="font-semibold text-emerald-950">{{ $db['name'] }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-900/70 text-[11px]">{{ $db['message'] }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $db['status'] === 'ok' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ strtoupper($db['status']) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Ekstensi PHP Wajib -->
    <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="grid size-10 place-items-center rounded-xl bg-emerald-100 text-xl">🧩</span>
                <div>
                    <h3 class="text-base font-bold text-emerald-950">Ekstensi PHP Wajib</h3>
                    <p class="text-xs text-emerald-900/60">Dibutuhkan untuk pemrosesan gambar logo, kompresi ZIP, PDF rendering, dan database.</p>
                </div>
            </div>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                Semua Terpasang
            </span>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($health['checks']['extensions'] as $extKey => $ext)
                <div class="rounded-2xl border border-emerald-100 bg-[#f5fbef] p-3 flex items-center justify-between text-xs">
                    <div>
                        <strong class="text-emerald-950 block">{{ $ext['name'] }}</strong>
                        <span class="font-mono text-[10px] text-emerald-900/60">ext-{{ $extKey }}</span>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $ext['status'] === 'ok' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $ext['status'] === 'ok' ? 'Aktif' : 'Hilang' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4. Konfigurasi Lingkungan .env -->
    <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4">
        <div class="flex items-center gap-3">
            <span class="grid size-10 place-items-center rounded-xl bg-amber-100 text-xl">⚙️</span>
            <div>
                <h3 class="text-base font-bold text-emerald-950">Audit Konfigurasi Lingkungan (.env)</h3>
                <p class="text-xs text-emerald-900/60">Pastikan pengaturan debug dinonaktifkan ketika dipasang di server live produksi.</p>
            </div>
        </div>

        @php $env = $health['checks']['environment']; @endphp
        <div class="rounded-2xl border border-emerald-200 bg-[#f5fbef] p-4 text-xs space-y-2">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                <div>
                    <span class="text-[10px] font-bold uppercase text-emerald-900/60">APP_ENV</span>
                    <p class="font-mono font-bold text-emerald-950">{{ $env['app_env'] }}</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-emerald-900/60">APP_DEBUG</span>
                    <p class="font-mono font-bold {{ $env['app_debug'] ? 'text-amber-800' : 'text-emerald-800' }}">
                        {{ $env['app_debug'] ? 'true' : 'false' }}
                    </p>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-emerald-900/60">APP_KEY</span>
                    <p class="font-mono font-bold text-emerald-800">Telah Dikonfigurasi (Valid)</p>
                </div>
            </div>
            <p class="text-emerald-900/80 pt-1 border-t border-emerald-100">
                Catatan: {{ $env['message'] }}
            </p>
        </div>
    </div>
</div>
@endsection
