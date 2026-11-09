@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Manajemen Kelas')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Rombongan Belajar</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Kelola Kelas Madrasah</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Kelola kelas tingkat 1 sampai 6 beserta rombel (misalnya 1A, 4B, dsb.) per tahun ajaran.</p>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Form Tambah Kelas -->
            <div class="lg:col-span-1">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm sticky top-24">
                    <h2 class="text-lg font-bold text-emerald-950">Tambah Kelas Baru</h2>
                    <p class="text-xs text-emerald-900/60 mt-1">Masukkan tingkat dan nama rombel yang sesuai.</p>

                    <form method="POST" action="{{ route('admin.school-classes.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="academic_year_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">Tahun Ajaran</label>
                            <select id="academic_year_id"
                                    name="academic_year_id"
                                    class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                    required>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year_id')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="grade_level" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">Tingkat Kelas (1–6)</label>
                            <select id="grade_level"
                                    name="grade_level"
                                    class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                    required>
                                @for ($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ old('grade_level') == $i ? 'selected' : '' }}>
                                        Tingkat {{ $i }} (Kelas {{ $i }})
                                    </option>
                                @endfor
                            </select>
                            @error('grade_level')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">Nama Rombel / Kelas</label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: 1A, 4B, atau 6 Abu Bakar"
                                   class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('name') border-rose-400 @enderror"
                                   required>
                            @error('name')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-950/10 transition hover:bg-emerald-900">
                            + Simpan Kelas
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabel Daftar Kelas & Filter -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Filter Bar -->
                <div class="rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-sm">
                    <form method="GET" action="{{ route('admin.school-classes.index') }}" class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Filter Tahun Ajaran</label>
                            <select name="academic_year_id" onchange="this.form.submit()" class="mt-1 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">Semua Tahun Ajaran</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Filter Tingkat</label>
                            <select name="grade_level" onchange="this.form.submit()" class="mt-1 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">Semua Tingkat (1–6)</option>
                                @for ($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ $selectedGrade == $i ? 'selected' : '' }}>Tingkat {{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Cari Nama Kelas</label>
                            <div class="mt-1 flex gap-2">
                                <input type="text"
                                       name="q"
                                       value="{{ $search }}"
                                       placeholder="Cari kelas..."
                                       class="w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <button type="submit" class="rounded-xl bg-emerald-800 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-900">
                                    Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabel Kelas -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-emerald-950">
                            <thead class="border-b border-emerald-100 bg-emerald-50/60 text-xs uppercase font-bold text-emerald-900/70">
                                <tr>
                                    <th class="px-4 py-3">Tingkat</th>
                                    <th class="px-4 py-3">Nama Kelas</th>
                                    <th class="px-4 py-3">Tahun Ajaran</th>
                                    <th class="px-4 py-3">Siswa Ditempatkan</th>
                                    <th class="px-4 py-3">Mapel Ditugaskan</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100/60">
                                @forelse ($classes as $class)
                                    <tr class="hover:bg-emerald-50/40">
                                        <td class="px-4 py-3">
                                            <span class="inline-flex size-7 place-items-center justify-center rounded-lg bg-emerald-100 font-bold text-emerald-800">
                                                {{ $class->grade_level }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-bold text-emerald-950">
                                            Kelas {{ $class->name }}
                                        </td>
                                        <td class="px-4 py-3 text-emerald-900/70 font-medium">
                                            {{ $class->academicYear?->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.enrollments.index', ['school_class_id' => $class->id]) }}" class="font-semibold text-emerald-700 hover:underline">
                                                {{ $class->enrollments_count }} siswa
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.subject-assignments.index', ['school_class_id' => $class->id]) }}" class="font-semibold text-amber-700 hover:underline">
                                                {{ $class->subject_assignments_count }} mapel
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <!-- Modal / detail edit -->
                                                <details class="relative text-left">
                                                    <summary class="cursor-pointer list-none rounded-lg border border-emerald-200 px-2.5 py-1 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
                                                        Ubah
                                                    </summary>
                                                    <div class="absolute right-0 top-8 z-30 w-72 rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-xl">
                                                        <h4 class="text-xs font-bold uppercase text-emerald-950">Edit Kelas {{ $class->name }}</h4>
                                                        <form method="POST" action="{{ route('admin.school-classes.update', $class) }}" class="mt-3 space-y-3">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="academic_year_id" value="{{ $class->academic_year_id }}">
                                                            <div>
                                                                <label class="block text-[10px] font-bold uppercase text-emerald-900/60">Tingkat (1–6)</label>
                                                                <select name="grade_level" class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs font-medium">
                                                                    @for ($i = 1; $i <= 6; $i++)
                                                                        <option value="{{ $i }}" {{ $class->grade_level == $i ? 'selected' : '' }}>Tingkat {{ $i }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-bold uppercase text-emerald-900/60">Nama Kelas</label>
                                                                <input type="text" name="name" value="{{ $class->name }}" class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs font-medium" required>
                                                            </div>
                                                            <button type="submit" class="w-full rounded-lg bg-emerald-800 py-1.5 text-xs font-bold text-white hover:bg-emerald-900">
                                                                Simpan Perubahan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </details>

                                                <form method="POST" action="{{ route('admin.school-classes.destroy', $class) }}" onsubmit="return confirm('Hapus kelas {{ $class->name }}? Penghapusan akan ditolak bila sudah memiliki riwayat penempatan siswa atau mapel.')">
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
                                            Tidak ada kelas yang ditemukan untuk kriteria filter ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $classes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
