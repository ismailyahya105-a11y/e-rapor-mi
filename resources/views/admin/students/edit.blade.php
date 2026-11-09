@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Ubah Data Siswa — ' . $student->name)

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.students.show', $student) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                    ← Kembali ke Detail Siswa
                </a>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl mt-1">Ubah Data Siswa</h1>
                <p class="text-sm text-emerald-900/60 mt-0.5">{{ $student->name }} (NIS: {{ $student->nis }})</p>
            </div>
        </div>

        <div class="rounded-3xl border border-emerald-950/10 bg-white p-8 shadow-sm">
            <form method="POST" action="{{ route('admin.students.update', $student) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="nis" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                            Nomor Induk Siswa (NIS) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               id="nis"
                               name="nis"
                               value="{{ old('nis', $student->nis) }}"
                               class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('nis') border-rose-400 @enderror"
                               required>
                        <span class="text-[11px] text-emerald-900/50 mt-1 block">Disimpan sebagai teks agar angka 0 di awal tetap utuh.</span>
                        @error('nis')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nisn" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                            NISN <span class="text-xs font-normal text-emerald-900/40">(Opsional)</span>
                        </label>
                        <input type="text"
                               id="nisn"
                               name="nisn"
                               value="{{ old('nisn', $student->nisn) }}"
                               class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('nisn') border-rose-400 @enderror">
                        @error('nisn')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Nama Lengkap Siswa <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $student->name) }}"
                           class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('name') border-rose-400 @enderror"
                           required>
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="gender" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <select id="gender"
                                name="gender"
                                class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('gender') border-rose-400 @enderror"
                                required>
                            <option value="Laki-laki" {{ old('gender', $student->gender) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender', $student->gender) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                            Status Siswa <span class="text-rose-500">*</span>
                        </label>
                        <select id="status"
                                name="status"
                                class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('status') border-rose-400 @enderror"
                                required>
                            <option value="aktif" {{ old('status', $student->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="lulus" {{ old('status', $student->status) === 'lulus' ? 'selected' : '' }}>Lulus</option>
                            <option value="mutasi" {{ old('status', $student->status) === 'mutasi' ? 'selected' : '' }}>Mutasi</option>
                            <option value="nonaktif" {{ old('status', $student->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="birth_place" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                            Tempat Lahir
                        </label>
                        <input type="text"
                               id="birth_place"
                               name="birth_place"
                               value="{{ old('birth_place', $student->birth_place) }}"
                               class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        @error('birth_place')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="birth_date" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                            Tanggal Lahir
                        </label>
                        <input type="date"
                               id="birth_date"
                               name="birth_date"
                               value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}"
                               class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        @error('birth_date')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Alamat Tempat Tinggal
                    </label>
                    <textarea id="address"
                              name="address"
                              rows="3"
                              class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">{{ old('address', $student->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-emerald-100">
                    <a href="{{ route('admin.students.show', $student) }}" class="rounded-xl border border-emerald-200 px-5 py-2.5 text-sm font-semibold text-emerald-900 hover:bg-emerald-50">
                        Batal
                    </a>
                    <button type="submit" class="rounded-xl bg-emerald-800 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-950/15 hover:bg-emerald-900 transition">
                        Perbarui Data Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
