@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Akun Wali & Hubungan Anak')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Akses Orang Tua / Wali</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Akun Wali & Hubungan Anak</h1>
                <p class="text-sm text-emerald-900/60 mt-1">
                    Kelola akun portal wali murid dan hubungkan dengan satu atau beberapa siswa yang bersekolah di madrasah.
                </p>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Form Buat Akun Wali Baru -->
            <div class="lg:col-span-1">
                <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm sticky top-24">
                    <h2 class="text-lg font-bold text-emerald-950">Buat Akun Wali Baru</h2>
                    <p class="text-xs text-emerald-900/60 mt-1">Buat kredensial login wali dan langsung hubungkan dengan anak.</p>

                    <form method="POST" action="{{ route('admin.guardians.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Nama Lengkap Wali <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: Bpk. Hendra Gunawan"
                                   class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('name') border-rose-400 @enderror"
                                   required>
                            @error('name')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Alamat Email (Login) <span class="text-rose-500">*</span>
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="Contoh: wali.hendra@example.com"
                                   class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('email') border-rose-400 @enderror"
                                   required>
                            @error('email')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Kata Sandi Awal <span class="text-rose-500">*</span>
                            </label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   placeholder="Minimal 6 karakter"
                                   class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('password') border-rose-400 @enderror"
                                   required>
                            @error('password')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="border-t border-emerald-100 pt-3">
                            <label for="student_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Hubungkan ke Siswa (Opsional)
                            </label>
                            <select id="student_id"
                                    name="student_id"
                                    class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">-- Pilih Anak / Siswa --</option>
                                @foreach ($allStudents as $st)
                                    <option value="{{ $st->id }}" {{ old('student_id') == $st->id ? 'selected' : '' }}>
                                        {{ $st->name }} (NIS: {{ $st->nis }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="relationship" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                                Hubungan Keluarga
                            </label>
                            <select id="relationship"
                                    name="relationship"
                                    class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <option value="Ayah">Ayah</option>
                                <option value="Ibu">Ibu</option>
                                <option value="Wali">Wali</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-950/10 transition hover:bg-emerald-900">
                            + Daftarkan Akun Wali
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabel Akun Wali & Anak Terhubung -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Search Bar -->
                <div class="rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-sm">
                    <form method="GET" action="{{ route('admin.guardians.index') }}" class="flex gap-2">
                        <input type="text"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Cari berdasarkan nama atau email wali..."
                               class="w-full rounded-xl border border-emerald-200 px-3.5 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <button type="submit" class="rounded-xl bg-emerald-800 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-900">
                            Cari
                        </button>
                    </form>
                </div>

                <!-- List Akun Wali Cards -->
                <div class="space-y-4">
                    @forelse ($guardians as $guardian)
                        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-emerald-100 pb-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-11 place-items-center rounded-2xl bg-amber-100 text-lg font-black text-emerald-900">
                                        {{ strtoupper(mb_substr($guardian->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <h3 class="text-base font-bold text-emerald-950">{{ $guardian->name }}</h3>
                                        <p class="text-xs text-emerald-900/60">{{ $guardian->email }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                        {{ $guardian->students_count }} Anak Terhubung
                                    </span>

                                    <!-- Modal Tambah Hubungan Siswa ke Wali Existing -->
                                    <details class="relative text-left">
                                        <summary class="cursor-pointer list-none rounded-xl bg-emerald-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-900 transition shadow-sm">
                                            + Tautkan Anak
                                        </summary>
                                        <div class="absolute right-0 top-9 z-30 w-80 rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-xl">
                                            <h4 class="text-xs font-bold uppercase text-emerald-950">
                                                Tautkan Siswa ke {{ $guardian->name }}
                                            </h4>
                                            <form method="POST" action="{{ route('admin.guardians.link-student', $guardian) }}" class="mt-3 space-y-3">
                                                @csrf
                                                <div>
                                                    <label class="block text-[10px] font-bold uppercase text-emerald-900/60">Pilih Siswa</label>
                                                    <select name="student_id" class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs font-medium" required>
                                                        <option value="">-- Pilih Siswa --</option>
                                                        @foreach ($allStudents as $st)
                                                            <option value="{{ $st->id }}">
                                                                {{ $st->name }} (NIS: {{ $st->nis }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold uppercase text-emerald-900/60">Hubungan</label>
                                                    <select name="relationship" class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs font-medium">
                                                        <option value="Ayah">Ayah</option>
                                                        <option value="Ibu">Ibu</option>
                                                        <option value="Wali">Wali</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="w-full rounded-lg bg-emerald-800 py-1.5 text-xs font-bold text-white hover:bg-emerald-900">
                                                    Simpan Hubungan
                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                </div>
                            </div>

                            <!-- Daftar Anak yang Terhubung -->
                            <div class="mt-4">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-900/60 mb-2">Anak yang Terhubung:</p>
                                @if ($guardian->students->isNotEmpty())
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        @foreach ($guardian->students as $child)
                                            <div class="flex items-center justify-between rounded-xl border border-emerald-100 bg-[#f5fbef] p-3">
                                                <div>
                                                    <a href="{{ route('admin.students.show', $child) }}" class="text-xs font-bold text-emerald-950 hover:underline">
                                                        {{ $child->name }}
                                                    </a>
                                                    <div class="flex items-center gap-2 text-[10px] text-emerald-900/60 mt-0.5">
                                                        <span>NIS: {{ $child->nis }}</span>
                                                        <span>•</span>
                                                        <span class="font-semibold text-emerald-800">{{ $child->pivot->relationship ?? 'Wali' }}</span>
                                                    </div>
                                                </div>

                                                <form method="POST" action="{{ route('admin.guardians.unlink-student', ['guardian' => $guardian, 'student' => $child]) }}" onsubmit="return confirm('Putuskan hubungan anak {{ $child->name }} dari wali ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800" title="Putuskan hubungan">
                                                        ✕
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50/40 p-3 text-center text-xs text-emerald-900/60">
                                        Belum ada anak yang ditautkan ke akun wali ini. Klik <strong>+ Tautkan Anak</strong> untuk menghubungkan siswa.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-3xl border border-dashed border-emerald-200 bg-white p-8 text-center text-emerald-900/60">
                            Belum ada akun wali murid yang terdaftar.
                        </div>
                    @endforelse

                    <div class="mt-4">
                        {{ $guardians->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
