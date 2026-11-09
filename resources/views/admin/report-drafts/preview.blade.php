@extends('layouts.dashboard')

@section('content')
    @include('admin.report-drafts.partials.checklist')
    <div class="mx-auto max-w-4xl print:max-w-none">
        <div class="mb-4 print:hidden"><button onclick="window.print()">Cetak Pratinjau</button></div>
        <article class="bg-white p-10 shadow print:p-0 print:shadow-none">
            <header class="border-b-2 border-slate-900 pb-4 text-center"><h1 class="text-xl font-bold">{{ data_get($draft->snapshot, 'school.name', 'Identitas Madrasah Belum Lengkap') }}</h1><p>DRAF — DATA DEMO</p></header>
            <section class="mt-6 grid grid-cols-2 gap-2"><p>Nama: {{ data_get($draft->snapshot, 'student.name') }}</p><p>NIS: {{ data_get($draft->snapshot, 'student.nis') }}</p><p>Kelas: {{ data_get($draft->snapshot, 'enrollment.class_name') }}</p><p>Semester: {{ data_get($draft->snapshot, 'enrollment.semester') }}</p></section>
            <h2 class="mt-8 font-bold">Nilai Akademik</h2><table class="mt-2 w-full border-collapse"><tbody>@forelse(data_get($draft->snapshot, 'academics', []) as $item)<tr><td class="border p-2">{{ $item['subject'] }}</td><td class="border p-2">{{ $item['final_score'] ?? '—' }}</td><td class="border p-2">{{ $item['description'] ?: 'Belum tersedia' }}</td></tr>@empty<tr><td class="border p-2">Belum ada mata pelajaran.</td></tr>@endforelse</tbody></table>
            <section class="mt-8"><h2 class="font-bold">Sikap dan Kehadiran</h2><p>{{ data_get($draft->snapshot, 'attitude_summary', 'Belum tersedia') }}</p><p>Sakit: {{ data_get($draft->snapshot, 'attendance.sakit', 0) }}, Izin: {{ data_get($draft->snapshot, 'attendance.izin', 0) }}, Alpa: {{ data_get($draft->snapshot, 'attendance.alpa', 0) }}</p></section>
            <section class="mt-8"><h2 class="font-bold">Ekstrakurikuler dan Prestasi</h2><ul>@forelse(data_get($draft->snapshot, 'extracurriculars', []) as $item)<li>{{ $item['name'] }} — {{ $item['predicate'] }}</li>@empty<li>Tidak ada data ekstrakurikuler.</li>@endforelse</ul><ul>@forelse(data_get($draft->snapshot, 'achievements', []) as $item)<li>{{ $item['achievement_name'] }} — {{ $item['rank'] }}</li>@empty<li>Tidak ada data prestasi.</li>@endforelse</ul></section>
            <section class="mt-8"><h2 class="font-bold">Catatan Wali Kelas</h2><p>{{ data_get($draft->snapshot, 'homeroom_note.academic_progress', 'Belum tersedia') }}</p></section>
            <footer class="mt-12 grid grid-cols-2 text-center"><p>Wali Kelas<br><br>{{ data_get($draft->snapshot, 'signatories.homeroom_teacher.teacher_name', '-') }}</p><p>Kepala Madrasah<br><br>{{ data_get($draft->snapshot, 'signatories.principal.principal_name', '-') }}</p></footer>
        </article>
    </div>
@endsection
