@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Mata Pelajaran')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Kurikulum & Pembelajaran</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Master Mata Pelajaran</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Kelola daftar mata pelajaran madrasah beserta kode unik dan urutan rapor.</p>
            </div>

            <a href="{{ route('admin.subject-assignments.index') }}" class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-emerald-950 shadow-md shadow-amber-950/10 hover:bg-amber-300 transition self-start sm:self-auto">
                <span>📋</span>
                <span>Buka Penugasan Mapel ke Kelas →</span>
            </a>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Form Tambah Mapel -->
            <div class="lg:col-span-1">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm sticky top-24">
                    <h2 class="text-lg font-bold text-emerald-950">Tambah Mata Pelajaran</h2>
                    <p class="text-xs text-emerald-900/60 mt-1">Kode mata pelajaran harus unik dalam sistem.</p>

                    <form method="POST" action="{{ route('admin.subjects.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="code" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Kode Mata Pelajaran <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="code"
                                   name="code"
                                   value="{{ old('code') }}"
                                   placeholder="Contoh: PAI-01, MTK, B-IND"
                                   class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('code') border-rose-400 @enderror"
                                   required>
                            @error('code')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Nama Mata Pelajaran <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: Al-Qur'an Hadis, Matematika"
                                   class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('name') border-rose-400 @enderror"
                                   required>
                            @error('name')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="order" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Nomor Urut Tampilan di Rapor
                            </label>
                            <input type="number"
                                   id="order"
                                   name="order"
                                   value="{{ old('order', 1) }}"
                                   min="0"
                                   class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="size-4 rounded border-emerald-300 text-emerald-800 focus:ring-emerald-500">
                            <label for="is_active" class="text-xs font-semibold text-emerald-950">
                                Status Aktif (Tersedia untuk diajarkan)
                            </label>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-950/10 transition hover:bg-emerald-900">
                            + Simpan Mata Pelajaran
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabel Daftar Mapel -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Filter & Search -->
                <div class="rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-sm">
                    <form method="GET" action="{{ route('admin.subjects.index') }}" class="grid gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Cari Mata Pelajaran</label>
                            <div class="mt-1 flex gap-2">
                                <input type="text"
                                       name="q"
                                       value="{{ $search }}"
                                       placeholder="Cari kode atau nama mapel..."
                                       class="w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <button type="submit" class="rounded-xl bg-emerald-800 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-900">
                                    Cari
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Filter Status</label>
                            <select name="status" onchange="this.form.submit()" class="mt-1 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">Semua Status</option>
                                <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ $status === '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Tabel Data Mapel -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-emerald-950">
                            <thead class="border-b border-emerald-100 bg-emerald-50/60 text-xs uppercase font-bold text-emerald-900/70">
                                <tr>
                                    <th class="px-4 py-3">Urut</th>
                                    <th class="px-4 py-3">Kode</th>
                                    <th class="px-4 py-3">Nama Mata Pelajaran</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Kelas Menggunakan</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100/60">
                                @forelse ($subjects as $subject)
                                    <tr class="hover:bg-emerald-50/40">
                                        <td class="px-4 py-3 font-semibold text-emerald-900/60">{{ $subject->order }}</td>
                                        <td class="px-4 py-3 font-mono font-bold text-emerald-900">{{ $subject->code }}</td>
                                        <td class="px-4 py-3 font-bold text-emerald-950">{{ $subject->name }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $subject->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $subject->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-emerald-900/70">
                                            {{ $subject->subject_assignments_count }} kelas
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <!-- Modal Edit -->
                                                <details class="relative text-left">
                                                    <summary class="cursor-pointer list-none rounded-lg border border-emerald-200 px-2.5 py-1 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
                                                        Ubah
                                                    </summary>
                                                    <div class="absolute right-0 top-8 z-30 w-72 rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-xl">
                                                        <h4 class="text-xs font-bold uppercase text-emerald-950">Edit Mapel {{ $subject->code }}</h4>
                                                        <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="mt-3 space-y-3">
                                                            @csrf
                                                            @method('PUT')
                                                            <div>
                                                                <label class="block text-[10px] font-bold uppercase text-emerald-900/60">Kode Mapel</label>
                                                                <input type="text" name="code" value="{{ $subject->code }}" class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs font-medium" required>
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-bold uppercase text-emerald-900/60">Nama Mapel</label>
                                                                <input type="text" name="name" value="{{ $subject->name }}" class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs font-medium" required>
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-bold uppercase text-emerald-900/60">Urutan Rapor</label>
                                                                <input type="number" name="order" value="{{ $subject->order }}" class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs font-medium">
                                                            </div>
                                                            <div class="flex items-center gap-2 pt-1">
                                                                <input type="checkbox" name="is_active" value="1" id="edit_active_{{ $subject->id }}" {{ $subject->is_active ? 'checked' : '' }} class="size-3.5 rounded text-emerald-800">
                                                                <label for="edit_active_{{ $subject->id }}" class="text-xs font-medium">Aktif</label>
                                                            </div>
                                                            <button type="submit" class="w-full rounded-lg bg-emerald-800 py-1.5 text-xs font-bold text-white hover:bg-emerald-900">
                                                                Simpan Perubahan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </details>

                                                <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Hapus/nonaktifkan mapel {{ $subject->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-emerald-900/50">
                                            Tidak ada mata pelajaran yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $subjects->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
