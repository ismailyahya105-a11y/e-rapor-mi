@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Data Siswa')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Master Data</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Data Induk Siswa</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Kelola identitas resmi siswa madrasah (NIS, NISN, tempat/tanggal lahir, alamat, status, dan riwayat).</p>
            </div>

            <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-800 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-950/15 hover:bg-emerald-900 transition self-start sm:self-auto">
                <span>+</span>
                <span>Tambah Siswa Baru</span>
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.students.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-900/60">Pencarian Siswa</label>
                    <div class="mt-1 flex gap-2">
                        <input type="text"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Cari berdasarkan NIS, NISN, atau Nama Siswa..."
                               class="w-full rounded-xl border border-emerald-200 px-3.5 py-2 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <button type="submit" class="rounded-xl bg-emerald-800 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-900 transition">
                            Cari
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-900/60">Filter Status</label>
                    <select name="status" onchange="this.form.submit()" class="mt-1 w-full rounded-xl border border-emerald-200 px-3.5 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ $selectedStatus === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="lulus" {{ $selectedStatus === 'lulus' ? 'selected' : '' }}>Lulus</option>
                        <option value="mutasi" {{ $selectedStatus === 'mutasi' ? 'selected' : '' }}>Mutasi</option>
                        <option value="nonaktif" {{ $selectedStatus === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-900/60">Filter Jenis Kelamin</label>
                    <select name="gender" onchange="this.form.submit()" class="mt-1 w-full rounded-xl border border-emerald-200 px-3.5 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <option value="">Semua Gender</option>
                        <option value="Laki-laki" {{ $selectedGender === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ $selectedGender === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Tabel Siswa -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-emerald-950">
                    <thead class="border-b border-emerald-100 bg-emerald-50/60 text-xs uppercase font-bold text-emerald-900/70">
                        <tr>
                            <th class="px-4 py-3.5">NIS</th>
                            <th class="px-4 py-3.5">NISN</th>
                            <th class="px-4 py-3.5">Nama Lengkap</th>
                            <th class="px-4 py-3.5">Jenis Kelamin</th>
                            <th class="px-4 py-3.5">Kelas Semester Aktif</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Wali Terhubung</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-100/60">
                        @forelse ($students as $student)
                            @php
                                $currClass = $student->currentClass($activeSemester);
                            @endphp
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3 font-mono font-bold text-emerald-900">{{ $student->nis }}</td>
                                <td class="px-4 py-3 font-mono text-emerald-900/70">{{ $student->nisn ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.students.show', $student) }}" class="font-bold text-emerald-950 hover:text-emerald-700">
                                        {{ $student->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">{{ $student->gender }}</td>
                                <td class="px-4 py-3">
                                    @if ($currClass)
                                        <span class="inline-flex rounded-lg bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">
                                            {{ $currClass->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-emerald-900/40 italic">Belum ditempatkan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $student->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($student->guardians->isNotEmpty())
                                        <span class="text-xs font-medium text-emerald-900">
                                            {{ $student->guardians->pluck('name')->join(', ') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-rose-600/70 italic">Belum ada wali</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.students.show', $student) }}" class="rounded-lg border border-emerald-200 px-2.5 py-1 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
                                            Detail
                                        </a>
                                        <a href="{{ route('admin.students.edit', $student) }}" class="rounded-lg border border-emerald-200 px-2.5 py-1 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Hapus siswa {{ $student->name }}? Jika sudah memiliki penempatan kelas atau hubungan wali, gunakan ubah status ke nonaktif.')">
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
                                <td colspan="8" class="px-4 py-8 text-center text-emerald-900/50">
                                    Tidak ada data siswa yang cocok dengan kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $students->links() }}
            </div>
        </div>
    </div>
@endsection
