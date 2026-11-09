@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Penempatan Kelas Siswa')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Rombongan Belajar</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Penempatan Kelas Siswa</h1>
                <p class="text-sm text-emerald-900/60 mt-1">
                    Hubungkan siswa ke rombel kelas per semester. Riwayat semester lalu tetap tersimpan secara permanen.
                </p>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Form Tempatkan Siswa -->
            <div class="lg:col-span-1">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm sticky top-24">
                    <h2 class="text-lg font-bold text-emerald-950">Tempatkan Siswa ke Kelas</h2>
                    <p class="text-xs text-emerald-900/60 mt-1">Pilih semester, kelas yang sesuai tahun ajarannya, dan siswa.</p>

                    @if ($errors->any())
                        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700 space-y-1">
                            @foreach ($errors->all() as $err)
                                <p>• {{ $err }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.enrollments.store') }}" class="mt-5 space-y-4">
                        @csrf

                        <!-- Pilih Semester -->
                        <div>
                            <label for="form_semester_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Target Semester <span class="text-rose-500">*</span>
                            </label>
                            <select id="form_semester_id"
                                    name="semester_id"
                                    onchange="window.location.href='{{ route('admin.enrollments.index') }}?semester_id=' + this.value"
                                    class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                    required>
                                @foreach ($semesters as $sem)
                                    <option value="{{ $sem->id }}" {{ ($selectedSemester?->id == $sem->id) ? 'selected' : '' }}>
                                        {{ $sem->full_name }} {{ $sem->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pilih Kelas Sesuai Tahun Ajaran Semester -->
                        <div>
                            <label for="form_school_class_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Target Kelas (Tahun: {{ $selectedSemester?->academicYear?->name }}) <span class="text-rose-500">*</span>
                            </label>
                            <select id="form_school_class_id"
                                    name="school_class_id"
                                    class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                    required>
                                <option value="">-- Pilih Rombel Kelas --</option>
                                @foreach ($availableClasses as $class)
                                    <option value="{{ $class->id }}" {{ old('school_class_id', $selectedClassId) == $class->id ? 'selected' : '' }}>
                                        Kelas {{ $class->name }} (Tingkat {{ $class->grade_level }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pilih Siswa yang belum ditempatkan di semester ini -->
                        <div>
                            <label for="student_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Siswa (Belum Ditempatkan) <span class="text-rose-500">*</span>
                            </label>
                            @if ($unplacedStudents->isNotEmpty())
                                <select id="student_id"
                                        name="student_id"
                                        class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                        required>
                                    <option value="">-- Pilih Siswa ({{ $unplacedStudents->count() }} belum ada kelas) --</option>
                                    @foreach ($unplacedStudents as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->name }} (NIS: {{ $student->nis }})
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <div class="mt-1.5 rounded-xl border border-emerald-200 bg-emerald-50/60 p-3 text-xs text-emerald-900">
                                    ✓ Semua siswa aktif sudah ditempatkan pada semester ini!
                                </div>
                            @endif
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Catatan Tambahan (Opsional)
                            </label>
                            <input type="text"
                                   id="notes"
                                   name="notes"
                                   value="{{ old('notes') }}"
                                   placeholder="Contoh: Siswa pindahan / kelas reguler"
                                   class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>

                        <button type="submit"
                                @if($unplacedStudents->isEmpty()) disabled @endif
                                class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-950/10 transition hover:bg-emerald-900 disabled:opacity-50 disabled:cursor-not-allowed">
                            + Tempatkan Siswa ke Kelas
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabel Daftar Penempatan & Filter -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Filter Bar -->
                <div class="rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-sm">
                    <form method="GET" action="{{ route('admin.enrollments.index') }}" class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Semester</label>
                            <select name="semester_id" onchange="this.form.submit()" class="mt-1 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                @foreach ($semesters as $sem)
                                    <option value="{{ $sem->id }}" {{ $selectedSemester?->id == $sem->id ? 'selected' : '' }}>
                                        {{ $sem->full_name }} {{ $sem->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Filter Kelas</label>
                            <select name="school_class_id" onchange="this.form.submit()" class="mt-1 w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">Semua Kelas</option>
                                @foreach ($availableClasses as $class)
                                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                        Kelas {{ $class->name }} (Tingkat {{ $class->grade_level }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/60">Cari Siswa</label>
                            <div class="mt-1 flex gap-2">
                                <input type="text"
                                       name="q"
                                       value="{{ $search }}"
                                       placeholder="Nama / NIS..."
                                       class="w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <button type="submit" class="rounded-xl bg-emerald-800 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-900">
                                    Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabel Penempatan -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-emerald-950">
                            <thead class="border-b border-emerald-100 bg-emerald-50/60 text-xs uppercase font-bold text-emerald-900/70">
                                <tr>
                                    <th class="px-4 py-3">NIS</th>
                                    <th class="px-4 py-3">Nama Siswa</th>
                                    <th class="px-4 py-3">Kelas Ditempati</th>
                                    <th class="px-4 py-3">Semester</th>
                                    <th class="px-4 py-3">Catatan</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100/60">
                                @forelse ($enrollments as $enrollment)
                                    <tr class="hover:bg-emerald-50/40">
                                        <td class="px-4 py-3 font-mono font-bold text-emerald-900">
                                            {{ $enrollment->student->nis }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold">
                                            <a href="{{ route('admin.students.show', $enrollment->student) }}" class="hover:underline">
                                                {{ $enrollment->student->name }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-lg bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">
                                                Kelas {{ $enrollment->schoolClass->name }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-emerald-900/70">
                                            {{ $enrollment->semester->full_name }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-emerald-900/60">
                                            {{ $enrollment->notes ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <!-- Pindah Kelas Modal -->
                                                <details class="relative text-left">
                                                    <summary class="cursor-pointer list-none rounded-lg border border-emerald-200 px-2.5 py-1 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
                                                        Pindah Kelas
                                                    </summary>
                                                    <div class="absolute right-0 top-8 z-30 w-72 rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-xl">
                                                        <h4 class="text-xs font-bold uppercase text-emerald-950">
                                                            Pindahkan {{ $enrollment->student->name }}
                                                        </h4>
                                                        <form method="POST" action="{{ route('admin.enrollments.update', $enrollment) }}" class="mt-3 space-y-3">
                                                            @csrf
                                                            @method('PUT')
                                                            <div>
                                                                <label class="block text-[10px] font-bold uppercase text-emerald-900/60">Pilih Kelas Baru</label>
                                                                <select name="school_class_id" class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs font-medium" required>
                                                                    @foreach ($availableClasses as $c)
                                                                        <option value="{{ $c->id }}" {{ $enrollment->school_class_id == $c->id ? 'selected' : '' }}>
                                                                            Kelas {{ $c->name }} (Tingkat {{ $c->grade_level }})
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-bold uppercase text-emerald-900/60">Catatan Pindah</label>
                                                                <input type="text" name="notes" value="{{ $enrollment->notes }}" class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs font-medium">
                                                            </div>
                                                            <button type="submit" class="w-full rounded-lg bg-emerald-800 py-1.5 text-xs font-bold text-white hover:bg-emerald-900">
                                                                Simpan Pemindahan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </details>

                                                <form method="POST" action="{{ route('admin.enrollments.destroy', $enrollment) }}" onsubmit="return confirm('Hapus penempatan kelas siswa ini?')">
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
                                            Belum ada siswa yang ditempatkan pada semester dan filter kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $enrollments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
