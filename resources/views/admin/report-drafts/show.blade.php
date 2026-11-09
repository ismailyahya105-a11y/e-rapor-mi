@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Detail Draf Rapor — ' . $student->name)

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Penerbitan Rapor</p>
            <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Rapor Draf — {{ $student->name }}</h1>
            <p class="text-sm text-emerald-900/60 mt-1">
                NIS: {{ $student->nis }} • Kelas: {{ $enrollment->schoolClass->name }} • Semester: {{ $semester->name }}
            </p>
        </div>
        <a href="{{ route('admin.report-drafts.index') }}" class="text-xs font-bold text-emerald-700 hover:underline self-start sm:self-auto">
            ← Kembali ke Pengelolaan Rapor
        </a>
    </div>

    @include('admin.report-drafts.partials.checklist')

    @if ($publication)
        <div class="rounded-2xl border border-emerald-300 bg-emerald-50/90 p-5 space-y-3 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="inline-flex rounded-full bg-emerald-700 px-3 py-1 text-xs font-bold text-white">
                    Terbit
                </span>
                <span class="text-sm font-bold text-emerald-950">
                    Status: Terbit
                </span>
            </div>
            <p class="text-sm text-emerald-900">
                Waktu terbit: <strong>{{ $publication->published_at ? $publication->published_at->format('d/m/Y H:i') : '—' }}</strong>
            </p>
            <div class="pt-1">
                <a href="{{ route('admin.report-publications.download', $publication) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-800 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-950/10 hover:bg-emerald-900 transition">
                    <span>📄</span>
                    <span>Unduh PDF</span>
                </a>
            </div>

            <!-- Pelacakan Akses & Unduhan Wali Murid -->
            @php
                $latestDownload = $publication->latestDownloadLog;
                $allLogs = $publication->accessLogs()->with('user')->latest('downloaded_at')->get();
                $downloadCount = $allLogs->where('action', 'download')->count();
            @endphp
            <div class="mt-4 pt-3 border-t border-emerald-200/80 space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900/70">
                    Pelacakan Akses Wali Murid
                </span>

                @if ($latestDownload)
                    <div class="rounded-xl border border-emerald-200 bg-white p-3.5 space-y-1">
                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-900">
                            <span class="grid size-5 place-items-center rounded-full bg-emerald-100 text-emerald-800 text-[10px]">✓</span>
                            <span>Sudah diunduh oleh wali murid pada {{ $latestDownload->downloaded_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="text-xs text-emerald-900/70 pl-7">
                            Diunduh oleh: <strong>{{ $latestDownload->user?->name ?? 'Wali' }}</strong>
                            @if ($latestDownload->ip_address)
                                • IP: <span class="font-mono">{{ $latestDownload->ip_address }}</span>
                            @endif
                            • Total: {{ $downloadCount }} kali unduh
                        </p>
                    </div>
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-3 flex items-center gap-2.5 text-xs text-amber-900">
                        <span class="text-base">⏳</span>
                        <span><strong>Belum diunduh</strong> oleh wali murid.</span>
                    </div>
                @endif

                @if ($allLogs->isNotEmpty())
                    <details class="text-xs pt-1">
                        <summary class="cursor-pointer font-bold text-emerald-800 hover:underline">
                            Lihat Riwayat Akses Lengkap ({{ $allLogs->count() }} log)
                        </summary>
                        <div class="mt-2 space-y-1 rounded-xl bg-white border border-emerald-200 p-3">
                            @foreach ($allLogs as $log)
                                <div class="flex items-center justify-between text-[11px] text-emerald-950 border-b border-emerald-100/60 pb-1 last:border-none last:pb-0">
                                    <div>
                                        <span class="font-semibold">{{ $log->user?->name ?? 'Pengguna' }}</span>
                                        <span class="text-emerald-900/60">({{ $log->action === 'download' ? 'Unduh PDF' : 'Buka Detail' }})</span>
                                        @if ($log->ip_address)
                                            <span class="font-mono text-emerald-900/50">• IP: {{ $log->ip_address }}</span>
                                        @endif
                                    </div>
                                    <span class="text-emerald-900/60 font-mono">{{ $log->downloaded_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
        </div>
    @else
        @if ($draft && $check['status'] === 'Siap Ditinjau' && empty($check['source_has_changed']))
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50/80 p-5 space-y-3 shadow-sm">
                <div>
                    <h3 class="text-sm font-bold text-emerald-950">Draf Siap Diterbitkan</h3>
                    <p class="text-xs text-emerald-900/70 mt-0.5">Draf telah lengkap dan mutakhir. Anda dapat menerbitkan buku rapor resmi sekarang.</p>
                </div>
                <form method="POST" action="{{ route('admin.report-publications.store', $draft) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-800 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-950/10 hover:bg-emerald-900 transition">
                        <span>🚀</span>
                        <span>Terbitkan</span>
                    </button>
                </form>
            </div>
        @endif
    @endif

    <div class="rounded-2xl border border-emerald-950/10 bg-white p-5 space-y-4 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-emerald-950">Tindakan Pratinjau</h2>
            <p class="text-xs text-emerald-900/60 mt-0.5">
                {{ $draft ? 'Draf tersedia.' : 'Belum Disusun.' }} Membuat atau memperbarui pratinjau akan mengganti isi draf dengan data sumber terbaru.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="POST" action="{{ route('admin.report-drafts.refresh', ['student' => $student->id, 'semester_id' => $semester->id]) }}">
                @csrf
                <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-800 transition">
                    Perbarui Pratinjau
                </button>
            </form>

            @if ($draft)
                <a href="{{ route('admin.report-drafts.preview', ['student' => $student->id, 'semester_id' => $semester->id]) }}"
                   class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-100 transition">
                    Buka Pratinjau →
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
