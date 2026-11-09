@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Penugasan Mata Pelajaran ke Kelas')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Kurikulum & Pembelajaran</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Penugasan Mapel ke Kelas</h1>
                <p class="text-sm text-emerald-900/60 mt-1">
                    Tentukan mata pelajaran yang diajarkan pada setiap rombel kelas di semester terpilih sebagai fondasi pengisian nilai rapor.
                </p>
            </div>

            <a href="{{ route('admin.subjects.index') }}" class="text-xs font-bold text-emerald-700 hover:underline self-start sm:self-auto">
                ← Kelola Master Mata Pelajaran
            </a>
        </div>

        <!-- Filter Selector: Semester & Kelas -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.subject-assignments.index') }}" class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-900/60">Pilih Semester</label>
                    <select name="semester_id" onchange="this.form.submit()" class="mt-1 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $selectedSemester?->id == $sem->id ? 'selected' : '' }}>
                                {{ $sem->full_name }} {{ $sem->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-900/60">Pilih Rombel Kelas</label>
                    <select name="school_class_id" onchange="this.form.submit()" class="mt-1 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        @forelse ($availableClasses as $class)
                            <option value="{{ $class->id }}" {{ $selectedClass?->id == $class->id ? 'selected' : '' }}>
                                Kelas {{ $class->name }} (Tingkat {{ $class->grade_level }})
                            </option>
                        @empty
                            <option value="">Tidak ada kelas di tahun ajaran semester ini</option>
                        @endforelse
                    </select>
                </div>
            </form>
        </div>

        @if ($selectedClass && $selectedSemester)
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Form Penugasan Mapel Baru -->
                <div class="lg:col-span-1">
                    <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm sticky top-24">
                        <h2 class="text-lg font-bold text-emerald-950">Tugaskan Mapel ke Kelas</h2>
                        <p class="text-xs text-emerald-900/60 mt-1">
                            Pilih mata pelajaran yang akan diajarkan di <strong>Kelas {{ $selectedClass->name }}</strong> pada <strong>{{ $selectedSemester->full_name }}</strong>.
                        </p>

                        @if ($unassignedSubjects->isNotEmpty())
                            <form method="POST" action="{{ route('admin.subject-assignments.store') }}" class="mt-5 space-y-4">
                                @csrf
                                <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                                <input type="hidden" name="school_class_id" value="{{ $selectedClass->id }}">

                                <div class="max-h-72 overflow-y-auto space-y-2 border border-emerald-100 rounded-2xl p-3 bg-[#f5fbef]">
                                    @foreach ($unassignedSubjects as $subject)
                                        <label class="flex items-center gap-2.5 rounded-xl bg-white p-2.5 border border-emerald-100 hover:border-emerald-300 cursor-pointer transition">
                                            <input type="checkbox"
                                                   name="subject_ids[]"
                                                   value="{{ $subject->id }}"
                                                   class="size-4 rounded border-emerald-300 text-emerald-800 focus:ring-emerald-500">
                                            <div class="text-xs">
                                                <strong class="block text-emerald-950 font-bold">{{ $subject->name }}</strong>
                                                <span class="font-mono text-[10px] text-emerald-900/60">Kode: {{ $subject->code }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-950/10 transition hover:bg-emerald-900">
                                    + Tugaskan Mapel Terpilih
                                </button>
                            </form>
                        @else
                            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 text-center text-xs text-emerald-900">
                                ✓ Seluruh mata pelajaran aktif telah ditugaskan ke kelas ini!
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tabel Mapel yang Sudah Ditugaskan -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between pb-4 border-b border-emerald-100">
                            <div>
                                <h3 class="text-lg font-bold text-emerald-950">
                                    Mata Pelajaran Kelas {{ $selectedClass->name }}
                                </h3>
                                <p class="text-xs text-emerald-900/60 mt-0.5">
                                    Total {{ $assignments->count() }} mata pelajaran aktif terdaftar
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-sm text-emerald-950">
                                <thead class="border-b border-emerald-100 bg-emerald-50/60 text-xs uppercase font-bold text-emerald-900/70">
                                    <tr>
                                        <th class="px-4 py-3">Urutan Rapor</th>
                                        <th class="px-4 py-3">Kode</th>
                                        <th class="px-4 py-3">Nama Mata Pelajaran</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-emerald-100/60">
                                    @forelse ($assignments as $assignment)
                                        <tr class="hover:bg-emerald-50/40">
                                            <td class="px-4 py-3 font-semibold text-emerald-900/60">
                                                {{ $assignment->subject->order }}
                                            </td>
                                            <td class="px-4 py-3 font-mono font-bold text-emerald-900">
                                                {{ $assignment->subject->code }}
                                            </td>
                                            <td class="px-4 py-3 font-bold text-emerald-950">
                                                {{ $assignment->subject->name }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <form method="POST" action="{{ route('admin.subject-assignments.destroy', $assignment) }}" onsubmit="return confirm('Hapus penugasan mapel {{ $assignment->subject->name }} dari kelas ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">
                                                        Hapus Penugasan
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-emerald-900/50">
                                                Belum ada mata pelajaran yang ditugaskan ke Kelas {{ $selectedClass->name }}. Silakan pilih dari daftar di samping.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-emerald-200 bg-white p-8 text-center text-emerald-900/60">
                Silakan buat rombongan belajar kelas pada tahun ajaran semester ini terlebih dahulu sebelum menugaskan mata pelajaran.
            </div>
        @endif
    </div>
@endsection
