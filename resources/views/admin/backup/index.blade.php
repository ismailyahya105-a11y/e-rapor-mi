@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Manajemen Pencadangan (Backup)')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <!-- Topbar Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Utilitas & Keamanan Data</p>
            <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Pencadangan Sistem & Arsip Rapor</h1>
            <p class="text-sm text-emerald-900/60 mt-0.5">
                Kelola berkas cadangan database dan kemasan arsip ZIP seluruh buku rapor resmi yang telah terbit.
            </p>
        </div>
        <a href="{{ route('admin.system.status') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-white px-4 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-50 transition shadow-sm">
            <span>🛡️</span>
            <span>Audit Status Sistem</span>
        </a>
    </div>

    <!-- Panel Pemicu Pencadangan (Trigger Actions) -->
    <div class="grid gap-6 sm:grid-cols-2">
        <!-- Opsi 1: Backup Database -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <span class="grid size-12 place-items-center rounded-2xl bg-emerald-100 text-2xl">🗄️</span>
                    <div>
                        <h2 class="text-base font-bold text-emerald-950">Cadangkan Database</h2>
                        <span class="text-xs text-emerald-900/60">Snapshot Seluruh Data Sistem</span>
                    </div>
                </div>
                <p class="text-xs text-emerald-900/70 leading-relaxed pt-2">
                    Menyimpan salinan lengkap struktur tabel, data siswa, nilai akademik, penilaian karakter, serta riwayat publikasi ke dalam berkas cadangan aman di server.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.backup.database') }}" onsubmit="return confirm('Mulai proses pencadangan database?')">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-900 transition">
                    <span>💾</span>
                    <span>Proses Backup Database Sekarang</span>
                </button>
            </form>
        </div>

        <!-- Opsi 2: Backup Arsip ZIP PDF Rapor -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <span class="grid size-12 place-items-center rounded-2xl bg-amber-100 text-2xl">📦</span>
                    <div>
                        <h2 class="text-base font-bold text-emerald-950">Kemas Arsip PDF Rapor (ZIP)</h2>
                        <span class="text-xs text-emerald-900/60">Semua Berkas Rapor Resmi Terbit</span>
                    </div>
                </div>
                <p class="text-xs text-emerald-900/70 leading-relaxed pt-2">
                    Mengemas seluruh dokumen PDF rapor siswa yang telah diterbitkan resmi di direktori storage ke dalam satu berkas terkompresi ZIP untuk arsip cetak atau cadangan offline.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.backup.reports') }}" onsubmit="return confirm('Mulai proses pengemasan berkas PDF rapor ke ZIP?')">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-black text-emerald-950 shadow-sm hover:bg-amber-400 transition">
                    <span>📥</span>
                    <span>Kemas Arsip PDF Rapor (ZIP)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Berkas Cadangan Tersimpan -->
    <div class="rounded-3xl border border-emerald-950/10 bg-white shadow-sm overflow-hidden">
        <div class="p-6 border-b border-emerald-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-emerald-950">Riwayat Berkas Cadangan di Server</h2>
                <p class="text-xs text-emerald-900/60 mt-0.5">
                    Lokasi penyimpanan server: <code class="font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded">storage/app/private/backups/</code>
                </p>
            </div>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ count($backups) }} Berkas Tersimpan
            </span>
        </div>

        @if (count($backups) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-emerald-50/70 text-emerald-900 font-bold uppercase text-[10px] tracking-wider border-b border-emerald-100">
                        <tr>
                            <th class="px-5 py-3 text-center w-12">No</th>
                            <th class="px-5 py-3">Nama Berkas</th>
                            <th class="px-5 py-3">Jenis Cadangan</th>
                            <th class="px-5 py-3 text-right">Ukuran</th>
                            <th class="px-5 py-3">Waktu Pembuatan</th>
                            <th class="px-5 py-3 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-100/60 text-emerald-950 font-medium">
                        @foreach ($backups as $index => $item)
                            <tr class="hover:bg-emerald-50/40 transition">
                                <td class="px-5 py-3.5 text-center text-emerald-900/60">{{ $index + 1 }}</td>
                                <td class="px-5 py-3.5 font-mono font-bold text-emerald-950 flex items-center gap-2">
                                    <span>{{ str_ends_with($item['filename'], '.zip') ? '📦' : '💾' }}</span>
                                    <span>{{ $item['filename'] }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ str_contains($item['type'], 'Database') ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                                        {{ $item['type'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono text-emerald-900">
                                    {{ $item['size_human'] }}
                                </td>
                                <td class="px-5 py-3.5 text-emerald-900/70">
                                    {{ $item['created_at']->translatedFormat('d M Y, H:i:s') }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.backup.download', $item['filename']) }}"
                                           class="rounded-lg bg-emerald-800 px-3 py-1.5 text-[11px] font-bold text-white hover:bg-emerald-900 transition">
                                            Unduh
                                        </a>

                                        <form method="POST" action="{{ route('admin.backup.destroy', $item['filename']) }}"
                                              onsubmit="return confirm('Hapus permanen berkas cadangan {{ $item['filename'] }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg border border-rose-300 bg-white px-2.5 py-1.5 text-[11px] font-bold text-rose-700 hover:bg-rose-50 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center space-y-2">
                <span class="inline-grid size-12 place-items-center rounded-2xl bg-amber-100 text-2xl">📁</span>
                <h3 class="text-base font-bold text-emerald-950">Belum Ada Berkas Cadangan</h3>
                <p class="text-xs text-emerald-900/60 max-w-sm mx-auto">
                    Gunakan tombol di atas untuk membuat cadangan database atau arsip ZIP PDF rapor pertama Anda.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
