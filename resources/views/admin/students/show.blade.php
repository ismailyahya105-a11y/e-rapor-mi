@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Detail Siswa — ' . $student->name)

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <!-- Top Nav & Actions -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('admin.students.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                    ← Kembali ke Daftar Siswa
                </a>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl mt-1">{{ $student->name }}</h1>
                <p class="text-sm text-emerald-900/60 mt-0.5">NIS: <span class="font-mono font-bold">{{ $student->nis }}</span> | NISN: <span class="font-mono">{{ $student->nisn ?? '-' }}</span></p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.students.edit', $student) }}" class="rounded-xl border border-emerald-300 bg-white px-4 py-2 text-xs font-bold text-emerald-900 hover:bg-emerald-50 shadow-sm transition">
                    Ubah Data Siswa
                </a>
                <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Hapus siswa {{ $student->name }}? Jika sudah memiliki penempatan kelas atau hubungan wali, penghapusan akan ditolak demi integritas data.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-rose-200 bg-white px-4 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50 shadow-sm transition">
                        Hapus Siswa
                    </button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Biodata Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-4 border-b border-emerald-100 pb-5">
                        <span class="grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-amber-200 to-amber-300 text-2xl font-black text-emerald-950 shadow-sm">
                            {{ strtoupper(mb_substr($student->name, 0, 1)) }}
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-emerald-950">{{ $student->name }}</h2>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $student->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                Status: {{ ucfirst($student->status) }}
                            </span>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-3 text-xs">
                        <div>
                            <dt class="font-bold uppercase tracking-wider text-emerald-900/60">Jenis Kelamin</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-emerald-950">{{ $student->gender }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold uppercase tracking-wider text-emerald-900/60">Tempat, Tanggal Lahir</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-emerald-950">
                                {{ $student->birth_place ?? '-' }}, {{ $student->birth_date ? $student->birth_date->translatedFormat('d F Y') : '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-bold uppercase tracking-wider text-emerald-900/60">Alamat</dt>
                            <dd class="mt-0.5 text-sm text-emerald-950 leading-relaxed">{{ $student->address ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Card Wali Terhubung -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-emerald-950 uppercase tracking-wider">Wali Terhubung</h3>
                        <a href="{{ route('admin.guardians.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                            + Kelola Wali
                        </a>
                    </div>

                    @forelse ($student->guardians as $guardian)
                        <div class="flex items-center justify-between rounded-2xl border border-emerald-100 bg-[#f5fbef] p-3 mb-2">
                            <div>
                                <strong class="block text-xs font-bold text-emerald-950">{{ $guardian->name }}</strong>
                                <span class="text-[11px] text-emerald-900/60">{{ $guardian->email }}</span>
                            </div>
                            <span class="rounded-full bg-emerald-800 px-2 py-0.5 text-[10px] font-bold text-white">
                                {{ $guardian->pivot->relationship ?? 'Wali' }}
                            </span>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/50 p-4 text-center text-xs text-emerald-900/60">
                            Belum ada akun wali yang terhubung ke siswa ini.
                            <a href="{{ route('admin.guardians.index') }}" class="mt-1 block font-bold text-emerald-800 hover:underline">Tautkan akun wali sekarang →</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Kolom Kanan: Kelas Aktif & Riwayat Semester -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Status Penempatan Semester Aktif -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Periode Saat Ini</span>
                            <h2 class="text-lg font-bold text-emerald-950">
                                {{ $activeSemester ? $activeSemester->full_name : 'Belum Ada Semester Aktif' }}
                            </h2>
                        </div>

                        <a href="{{ route('admin.enrollments.index') }}" class="rounded-xl bg-emerald-800 px-3.5 py-1.5 text-xs font-bold text-white shadow hover:bg-emerald-900">
                            + Atur Penempatan
                        </a>
                    </div>

                    <div class="mt-4 rounded-2xl border border-emerald-100 bg-[#f5fbef] p-4">
                        @if ($currentEnrollment)
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <p class="text-xs text-emerald-900/60">Terdaftar di kelas:</p>
                                    <strong class="text-xl font-black text-emerald-950">
                                        Kelas {{ $currentEnrollment->schoolClass->name }} (Tingkat {{ $currentEnrollment->schoolClass->grade_level }})
                                    </strong>
                                    @if ($currentEnrollment->notes)
                                        <p class="mt-1 text-xs text-emerald-900/70">Catatan: {{ $currentEnrollment->notes }}</p>
                                    @endif
                                </div>
                                <span class="self-start rounded-full bg-emerald-800 px-3 py-1 text-xs font-bold text-amber-300">
                                    Aktif Semester Ini
                                </span>
                            </div>
                        @else
                            <div class="text-center py-2 text-xs text-emerald-900/60">
                                Siswa ini belum ditempatkan ke kelas mana pun pada semester aktif saat ini.
                                <a href="{{ route('admin.enrollments.index') }}" class="mt-1 block font-bold text-emerald-800 hover:underline">
                                    Buka menu penempatan kelas →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Riwayat Penempatan Antarsemester -->
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-emerald-950">Riwayat Penempatan Kelas Antarsemester</h2>
                    <p class="text-xs text-emerald-900/60 mt-1">Daftar kelas yang pernah ditempati siswa di setiap semester tanpa menimpa data sebelumnya.</p>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm text-emerald-950">
                            <thead class="border-b border-emerald-100 bg-emerald-50/60 text-xs uppercase font-bold text-emerald-900/70">
                                <tr>
                                    <th class="px-4 py-3">Tahun Ajaran</th>
                                    <th class="px-4 py-3">Semester</th>
                                    <th class="px-4 py-3">Kelas / Rombel</th>
                                    <th class="px-4 py-3">Catatan</th>
                                    <th class="px-4 py-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100/60">
                                @forelse ($student->enrollments->sortByDesc('id') as $enrollment)
                                    <tr class="hover:bg-emerald-50/40">
                                        <td class="px-4 py-3 font-semibold">{{ $enrollment->semester->academicYear->name ?? '-' }}</td>
                                        <td class="px-4 py-3">Semester {{ $enrollment->semester->name ?? '-' }}</td>
                                        <td class="px-4 py-3 font-bold text-emerald-900">
                                            Kelas {{ $enrollment->schoolClass->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-emerald-900/70">{{ $enrollment->notes ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            @if ($enrollment->semester?->is_active)
                                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800">
                                                    Semester Aktif
                                                </span>
                                            @else
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">
                                                    Riwayat Lalu
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-emerald-900/50">
                                            Belum ada riwayat penempatan kelas untuk siswa ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
