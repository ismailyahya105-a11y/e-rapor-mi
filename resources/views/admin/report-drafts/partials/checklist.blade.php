<section class="rounded-2xl border border-emerald-950/10 bg-white p-5 space-y-3 shadow-sm">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-bold text-emerald-950">Status Kelengkapan Draf</h2>
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $check['status'] === 'Siap Ditinjau' ? 'bg-emerald-100 text-emerald-800' : ($check['status'] === 'Memerlukan Perbaikan' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
            {{ $check['status'] }}
        </span>
    </div>

    @if (!empty($check['source_has_changed']))
        <div class="flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-xs text-amber-800 border border-amber-200">
            <span class="text-base leading-none">⚠️</span>
            <div>
                <strong>Data sumber berubah</strong> — Terdapat perubahan data sejak draf terakhir dibuat. Silakan klik tombol <em>Perbarui Pratinjau</em> di bawah sebelum menerbitkan.
            </div>
        </div>
    @endif

    @if (!empty($check['missing']))
        <div class="space-y-1 pt-1">
            <p class="text-xs font-semibold text-rose-800">Item data yang belum lengkap:</p>
            <ul class="list-disc pl-5 text-xs text-rose-700 space-y-0.5">
                @foreach ($check['missing'] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</section>
