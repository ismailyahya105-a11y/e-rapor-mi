@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Tahun Ajaran & Semester')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Manajemen Periode</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Tahun Ajaran & Semester</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Kelola kalender akademik madrasah. Hanya satu semester yang dapat aktif pada satu waktu.</p>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Form Tambah Tahun Ajaran -->
            <div class="lg:col-span-1">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-emerald-950">Tambah Tahun Ajaran Baru</h2>
                    <p class="text-xs text-emerald-900/60 mt-1">Sistem otomatis menyiapkan semester Ganjil dan Genap untuk tahun ajaran baru.</p>

                    <form method="POST" action="{{ route('admin.academic-years.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">Nama Tahun Ajaran</label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: 2025/2026"
                                   class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('name') border-rose-400 @enderror"
                                   required>
                            @error('name')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active') ? 'checked' : '' }}
                                   class="size-4 rounded border-emerald-300 text-emerald-800 focus:ring-emerald-500">
                            <label for="is_active" class="text-xs font-semibold text-emerald-950">
                                Aktifkan langsung semester Ganjil tahun ajaran ini
                            </label>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-950/10 transition hover:bg-emerald-900">
                            + Simpan Tahun Ajaran
                        </button>
                    </form>
                </div>
            </div>

            <!-- List Tahun Ajaran -->
            <div class="lg:col-span-2 space-y-4">
                @forelse ($academicYears as $year)
                    <div class="rounded-3xl border {{ $year->is_active ? 'border-amber-300 bg-amber-50/20' : 'border-emerald-950/10 bg-white' }} p-6 shadow-sm transition">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <span class="grid size-10 place-items-center rounded-2xl {{ $year->is_active ? 'bg-amber-300 text-emerald-950 font-black' : 'bg-emerald-100 text-emerald-800 font-bold' }}">
                                    🗓️
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-xl font-bold text-emerald-950">{{ $year->name }}</h3>
                                        @if ($year->is_active)
                                            <span class="rounded-full bg-amber-300 px-2.5 py-0.5 text-[11px] font-black text-emerald-950">
                                                TAHUN AKTIF
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-emerald-900/60 mt-0.5">
                                        {{ $year->school_classes_count }} kelas rombel terdaftar
                                    </p>
                                </div>
                            </div>

                            <!-- Tombol Hapus / Edit -->
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}" onsubmit="return confirm('Hapus tahun ajaran {{ $year->name }}? Penghapusan akan ditolak jika sudah memiliki riwayat kelas/penempatan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Semesters List -->
                        <div class="mt-5 border-t border-emerald-950/5 pt-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-900/60 mb-3">Daftar Semester</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @forelse ($year->semesters as $semester)
                                    <div class="flex items-center justify-between rounded-2xl border {{ $semester->is_active ? 'border-amber-400 bg-white shadow-sm ring-2 ring-amber-300/40' : 'border-emerald-100 bg-[#f5fbef]' }} p-3.5">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <strong class="text-sm font-bold text-emerald-950">Semester {{ $semester->name }}</strong>
                                                @if ($semester->is_active)
                                                    <span class="rounded-full bg-emerald-800 px-2 py-0.5 text-[10px] font-bold text-amber-300">
                                                        AKTIF
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-emerald-900/60 mt-0.5">
                                                {{ $year->name }}
                                            </p>
                                        </div>

                                        @if (! $semester->is_active)
                                            <form method="POST" action="{{ route('admin.semesters.activate', $semester) }}">
                                                @csrf
                                                <button type="submit" class="rounded-xl bg-emerald-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-900 transition shadow-sm">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs font-bold text-emerald-800">✓ Sedang Aktif</span>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-emerald-900/50">Belum ada semester dibuat.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-emerald-200 bg-white p-8 text-center text-emerald-900/60">
                        Belum ada tahun ajaran. Silakan tambahkan tahun ajaran pertama melalui formulir di sebelah kiri.
                    </div>
                @endforelse

                <div class="mt-4">
                    {{ $academicYears->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
