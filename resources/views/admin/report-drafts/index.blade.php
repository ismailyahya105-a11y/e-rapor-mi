@extends('layouts.dashboard')
@section('content')
<div class="space-y-6"><h1 class="text-2xl font-bold">Pengelolaan Rapor</h1>@if($semester)<div class="rounded-xl border bg-white"><table class="w-full text-left"><tbody>@forelse($enrollments as $enrollment)<tr class="border-b"><td class="p-3">{{ $enrollment->student->name }}</td><td>{{ $drafts->has($enrollment->student_id) ? 'Draf tersedia' : 'Belum Disusun' }}</td><td><a class="text-emerald-700" href="{{ route('admin.report-drafts.show', ['student' => $enrollment->student_id, 'semester_id' => $semester->id]) }}">Periksa</a></td></tr>@empty<tr><td class="p-4">Belum ada siswa.</td></tr>@endforelse</tbody></table></div>@else<div>Belum ada semester aktif.</div>@endif</div>
@endsection
