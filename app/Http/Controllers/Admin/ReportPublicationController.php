<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportDraft;
use App\Models\ReportPublication;
use App\Services\ReportCompletenessChecker;
use Dompdf\Dompdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportPublicationController extends Controller
{
    public function store(Request $request, ReportDraft $reportDraft, ReportCompletenessChecker $checker): RedirectResponse
    {
        if ($reportDraft->semester->isLocked()) {
            return back()->with('error', 'Semester ini telah dikunci/diarsipkan. Rapor tidak dapat diterbitkan ulang.');
        }

        $check = $checker->check($reportDraft->student, $reportDraft->semester, $reportDraft);
        if ($check['status'] !== 'Siap Ditinjau') {
            $reason = ! empty($check['missing'])
                ? implode(', ', $check['missing'])
                : (! empty($check['source_has_changed']) ? 'Data sumber telah berubah sejak draf terakhir dibuat' : 'Draf belum lengkap');

            return back()->with('error', 'Rapor belum dapat diterbitkan: '.$reason);
        }

        $path = null;
        try {
            $publication = DB::transaction(function () use ($reportDraft, $request, &$path): ReportPublication {
                $existing = ReportPublication::query()->where('report_draft_id', $reportDraft->id)->lockForUpdate()->first();
                if ($existing) {
                    return $existing;
                }
                $html = view('admin.report-publications.pdf', ['snapshot' => $reportDraft->snapshot])->render();
                $pdf = new Dompdf;
                $pdf->setPaper('A4');
                $pdf->loadHtml($html);
                $pdf->render();
                $path = 'reports/'.$reportDraft->id.'/'.uniqid('final-', true).'.pdf';
                Storage::put($path, $pdf->output());

                return ReportPublication::query()->create(['report_draft_id' => $reportDraft->id, 'student_id' => $reportDraft->student_id, 'semester_id' => $reportDraft->semester_id, 'published_by_user_id' => $request->user()->id, 'snapshot' => $reportDraft->snapshot, 'pdf_path' => $path, 'published_at' => now()]);
            });
        } catch (\Throwable $exception) {
            if ($path !== null) {
                Storage::delete($path);
            }
            throw $exception;
        }

        return back()->with('status', 'Rapor terbit.');
    }

    public function download(ReportPublication $reportPublication)
    {
        abort_unless(Storage::exists($reportPublication->pdf_path), 404);

        return Storage::download($reportPublication->pdf_path, 'rapor-'.$reportPublication->id.'.pdf');
    }
}
