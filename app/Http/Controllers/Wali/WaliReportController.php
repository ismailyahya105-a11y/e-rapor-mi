<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\ReportAccessLog;
use App\Models\ReportPublication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WaliReportController extends Controller
{
    /**
     * Tampilkan detail buku rapor resmi yang telah terbit.
     */
    public function show(Request $request, ReportPublication $reportPublication): View
    {
        Gate::authorize('view', $reportPublication);

        // Catat audit log saat wali melihat pratinjau/detail rapor
        ReportAccessLog::create([
            'report_publication_id' => $reportPublication->id,
            'user_id' => $request->user()->id,
            'action' => 'view',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'downloaded_at' => now(),
        ]);

        $reportPublication->load(['student', 'semester.academicYear']);

        return view('wali.reports.show', [
            'publication' => $reportPublication,
            'student' => $reportPublication->student,
            'semester' => $reportPublication->semester,
            'snapshot' => $reportPublication->snapshot,
        ]);
    }

    /**
     * Unduh file PDF rapor resmi siswa oleh wali murid.
     */
    public function download(Request $request, ReportPublication $reportPublication): StreamedResponse
    {
        Gate::authorize('download', $reportPublication);

        abort_unless(Storage::exists($reportPublication->pdf_path), 404, 'File dokumen rapor tidak ditemukan di server.');

        // Catat audit log unduhan resmi
        ReportAccessLog::create([
            'report_publication_id' => $reportPublication->id,
            'user_id' => $request->user()->id,
            'action' => 'download',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'downloaded_at' => now(),
        ]);

        $fileName = 'rapor-'.$reportPublication->student->nis.'-'.Str::slug($reportPublication->semester->name).'.pdf';

        return Storage::download($reportPublication->pdf_path, $fileName);
    }
}
