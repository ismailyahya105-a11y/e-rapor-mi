@extends('layouts.dashboard', ['isAdmin' => true])

@section('title', 'Catatan Wali Kelas')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Penilaian Nonakademik</p>
                <h1 class="text-2xl font-black tracking-tight text-emerald-950 sm:text-3xl">Catatan Wali Kelas</h1>
                <p class="text-sm text-emerald-900/60 mt-1">Berikan evaluasi komprehensif, motivasi belajar, dan saran membangun kepada orang tua santri/siswa.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('admin.non-academic-recap.index', ['semester_id' => $selectedSemester?->id, 'school_class_id' => $selectedClass?->id]) }}" class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-2.5 text-xs font-bold text-emerald-950 shadow-md shadow-amber-950/10 hover:bg-amber-300 transition">
                    <span>📋</span>
                    <span>Buka Rekap Nonakademik →</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.homeroom-notes.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="filter_semester" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Semester & Tahun Ajaran
                    </label>
                    <select id="filter_semester"
                            name="semester_id"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $selectedSemester && $selectedSemester->id === $sem->id ? 'selected' : '' }}>
                                {{ $sem->academicYear?->name ?? 'Tahun ?' }} — Sem. {{ $sem->term }} {{ $sem->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter_class" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Kelas
                    </label>
                    <select id="filter_class"
                            name="school_class_id"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-semibold text-emerald-950 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        @forelse ($availableClasses as $c)
                            <option value="{{ $c->id }}" {{ $selectedClass && $selectedClass->id === $c->id ? 'selected' : '' }}>
                                Kelas {{ $c->name }} (Tingkat {{ $c->grade_level }})
                            </option>
                        @empty
                            <option value="">Belum ada kelas</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label for="filter_search" class="block text-xs font-bold uppercase tracking-wider text-emerald-900/70">
                        Cari Siswa
                    </label>
                    <div class="mt-1.5 flex gap-2">
                        <input type="text"
                               id="filter_search"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Nama atau NIS..."
                               class="w-full rounded-xl border border-emerald-200 px-3.5 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <button type="submit" class="rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-900 transition">
                            Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if (!$selectedSemester || !$selectedClass)
            <div class="rounded-3xl border border-dashed border-emerald-300 bg-emerald-50/50 p-12 text-center">
                <span class="text-4xl">✍️</span>
                <h3 class="mt-3 text-base font-bold text-emerald-950">Pilih Semester dan Kelas</h3>
                <p class="text-xs text-emerald-900/60 mt-1">Pilih semester dan kelas untuk mengisi lembar catatan wali kelas.</p>
            </div>
        @elseif ($students->isEmpty())
            <div class="rounded-3xl border border-emerald-950/10 bg-white p-12 text-center text-emerald-900/50">
                Tidak ada siswa yang terdaftar di Kelas {{ $selectedClass->name }} untuk semester ini.
            </div>
        @else
            <!-- Form Catatan Wali Kelas Massal -->
            <form method="POST" action="{{ route('admin.homeroom-notes.store') }}">
                @csrf
                <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                <input type="hidden" name="school_class_id" value="{{ $selectedClass->id }}">

                <div class="space-y-6">
                    @foreach ($students as $idx => $student)
                        @php
                            $note = $existingNotes->get($student->id);
                        @endphp
                        <div class="rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-sm space-y-4 student-note-card" data-student-id="{{ $student->id }}">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-emerald-100 gap-2">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-8 place-items-center rounded-xl bg-emerald-100 font-black text-xs text-emerald-900">
                                        {{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <h3 class="text-base font-black text-emerald-950">{{ $student->name }}</h3>
                                        <div class="text-[11px] font-mono text-emerald-900/60">NIS: {{ $student->nis }} • Kelas {{ $selectedClass->name }}</div>
                                    </div>
                                </div>

                                <button type="button"
                                        onclick="applySampleNote('{{ $student->id }}', '{{ addslashes($student->name) }}')"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800 hover:bg-emerald-100 self-start sm:self-auto">
                                    💡 Contoh Draf Catatan
                                </button>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/70">
                                        1. Perkembangan Belajar
                                    </label>
                                    <textarea name="notes[{{ $student->id }}][academic_progress]"
                                              rows="2"
                                              placeholder="Progres capaian akademik & materi..."
                                              class="field-academic mt-1 w-full rounded-xl border border-emerald-200 p-2.5 text-xs text-emerald-950 focus:border-emerald-600 focus:outline-none">{{ $note?->academic_progress }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/70">
                                        2. Sikap dan Perilaku
                                    </label>
                                    <textarea name="notes[{{ $student->id }}][attitude_behavior]"
                                              rows="2"
                                              placeholder="Adab terhadap guru dan teman..."
                                              class="field-attitude mt-1 w-full rounded-xl border border-emerald-200 p-2.5 text-xs text-emerald-950 focus:border-emerald-600 focus:outline-none">{{ $note?->attitude_behavior }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/70">
                                        3. Kedisiplinan
                                    </label>
                                    <textarea name="notes[{{ $student->id }}][discipline]"
                                              rows="2"
                                              placeholder="Ketertiban kehadiran dan pakaian..."
                                              class="field-discipline mt-1 w-full rounded-xl border border-emerald-200 p-2.5 text-xs text-emerald-950 focus:border-emerald-600 focus:outline-none">{{ $note?->discipline }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/70">
                                        4. Potensi Siswa
                                    </label>
                                    <textarea name="notes[{{ $student->id }}][student_potential]"
                                              rows="2"
                                              placeholder="Bakat dan kelebihan menonjol..."
                                              class="field-potential mt-1 w-full rounded-xl border border-emerald-200 p-2.5 text-xs text-emerald-950 focus:border-emerald-600 focus:outline-none">{{ $note?->student_potential }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/70">
                                        5. Hal yang Perlu Ditingkatkan
                                    </label>
                                    <textarea name="notes[{{ $student->id }}][areas_for_improvement]"
                                              rows="2"
                                              placeholder="Aspek yang membutuhkan pembiasaan..."
                                              class="field-improvement mt-1 w-full rounded-xl border border-emerald-200 p-2.5 text-xs text-emerald-950 focus:border-emerald-600 focus:outline-none">{{ $note?->areas_for_improvement }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-900/70">
                                        6. Saran Kepada Orang Tua
                                    </label>
                                    <textarea name="notes[{{ $student->id }}][parent_advice]"
                                              rows="2"
                                              placeholder="Saran pendampingan di rumah..."
                                              class="field-parent mt-1 w-full rounded-xl border border-emerald-200 p-2.5 text-xs text-emerald-950 focus:border-emerald-600 focus:outline-none">{{ $note?->parent_advice }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Bottom Action Bar -->
                    <div class="sticky bottom-4 z-20 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-emerald-950/20 bg-emerald-950 p-4 text-white shadow-2xl">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 place-items-center rounded-xl bg-amber-400 text-emerald-950 font-black">✍️</span>
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-amber-400">Penyimpanan Terpadu</div>
                                <div class="text-xs text-emerald-100/80">Catatan wali kelas tersimpan per semester dan siap dicantumkan dalam buku rapor.</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit" class="rounded-xl bg-amber-400 px-6 py-2.5 text-xs font-black text-emerald-950 shadow-md shadow-amber-950/20 hover:bg-amber-300 transition">
                                Simpan Catatan Seluruh Siswa ({{ $students->count() }})
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <script>
        function applySampleNote(studentId, studentName) {
            const card = document.querySelector(`.student-note-card[data-student-id="${studentId}"]`);
            if (!card) return;

            card.querySelector('.field-academic').value = `${studentName} menunjukkan peningkatan yang sangat membanggakan dalam memahami materi pembelajaran, terutama hafalan dan literasi.`;
            card.querySelector('.field-attitude').value = 'Berperilaku sopan dan santun kepada para ustadz/ustadzah serta ramah terhadap teman sekelas.';
            card.querySelector('.field-discipline').value = 'Tertib dalam mematuhi tata tertib madrasah dan selalu hadir tepat waktu.';
            card.querySelector('.field-potential').value = 'Memiliki daya ingat hafalan yang tajam dan antusias dalam kegiatan keagamaan.';
            card.querySelector('.field-improvement').value = 'Perlu melatih ketelitian dalam pengerjaan soal hitungan matematika.';
            card.querySelector('.field-parent').value = 'Mohon pertahankan bimbingan mengaji dan mendengarkan murottal Al-Qur\'an secara rutin di rumah.';
        }
    </script>
@endsection

