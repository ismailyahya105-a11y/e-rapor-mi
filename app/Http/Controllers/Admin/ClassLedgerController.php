<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Services\ClassLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClassLedgerController extends Controller
{
    public function index(Request $request, ClassLedgerService $service): View
    {
        $activeSemester = Semester::query()->where('is_active', true)->with('academicYear')->first();
        $selectedSemesterId = $request->query('semester_id', $activeSemester?->id);
        $selectedClassId = $request->query('school_class_id');
        $sortByRank = $request->boolean('sort_by_rank', false);
        $showRanking = $request->boolean('show_ranking', true);

        $semesters = Semester::query()->with('academicYear')->orderByDesc('id')->get();
        $selectedSemester = $selectedSemesterId
            ? Semester::query()->with('academicYear')->find($selectedSemesterId)
            : $activeSemester;

        $availableClasses = $selectedSemester
            ? SchoolClass::query()->where('academic_year_id', $selectedSemester->academic_year_id)->orderBy('grade_level')->orderBy('name')->get()
            : collect();

        $selectedClass = $selectedClassId
            ? SchoolClass::query()->find($selectedClassId)
            : $availableClasses->first();

        $ledgerData = null;
        if ($selectedSemester && $selectedClass) {
            $ledgerData = $service->getLedgerData($selectedSemester, $selectedClass, $sortByRank);
        }

        return view('admin.leger.index', [
            'semesters' => $semesters,
            'selectedSemester' => $selectedSemester,
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'ledgerData' => $ledgerData,
            'sortByRank' => $sortByRank,
            'showRanking' => $showRanking,
        ]);
    }

    public function export(Request $request, ClassLedgerService $service): StreamedResponse
    {
        $selectedSemester = Semester::query()->with('academicYear')->findOrFail($request->integer('semester_id'));
        $selectedClass = SchoolClass::query()->findOrFail($request->integer('school_class_id'));
        $sortByRank = $request->boolean('sort_by_rank', false);

        $ledgerData = $service->getLedgerData($selectedSemester, $selectedClass, $sortByRank);

        $fileName = 'leger-nilai-kelas-'.Str::slug($selectedClass->name).'-'.Str::slug($selectedSemester->name).'.csv';

        return response()->streamDownload(function () use ($ledgerData): void {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header Dokumen
            fputcsv($handle, ['BUKU LEGER NILAI PESERTA DIDIK']);
            fputcsv($handle, ['Madrasah', data_get($ledgerData, 'school.name', 'Madrasah Ibtidaiyah')]);
            fputcsv($handle, ['Kelas', data_get($ledgerData, 'schoolClass.name')]);
            fputcsv($handle, ['Semester', data_get($ledgerData, 'semester.name').' ('.data_get($ledgerData, 'semester.academicYear.name').')']);
            fputcsv($handle, ['Wali Kelas', data_get($ledgerData, 'teacher.teacher_name', '-')]);
            fputcsv($handle, []); // Baris kosong

            // Kolom Header Tabel
            $header = ['No', 'NIS', 'NISN', 'Nama Siswa'];
            foreach ($ledgerData['subjects'] as $subject) {
                $header[] = $subject->name;
            }
            $header[] = 'Jumlah Nilai';
            $header[] = 'Rata-Rata';
            $header[] = 'Peringkat';
            $header[] = 'Sakit';
            $header[] = 'Izin';
            $header[] = 'Alpa';

            fputcsv($handle, $header);

            // Baris Data Siswa
            foreach ($ledgerData['rows'] as $idx => $row) {
                $line = [
                    $idx + 1,
                    $row['student']->nis,
                    $row['student']->nisn ?? '-',
                    $row['student']->name,
                ];

                foreach ($ledgerData['subjects'] as $subject) {
                    $score = $row['scores'][$subject->id] ?? '-';
                    $line[] = $score;
                }

                $line[] = $row['total_score'];
                $line[] = $row['average_score'];
                $line[] = $row['rank'];
                $line[] = $row['attendance']['sakit'];
                $line[] = $row['attendance']['izin'];
                $line[] = $row['attendance']['alpa'];

                fputcsv($handle, $line);
            }

            // Baris Statistik Kelas
            fputcsv($handle, []);
            $avgLine = ['', '', '', 'RATA-RATA KELAS'];
            $maxLine = ['', '', '', 'NILAI TERTINGGI'];
            $minLine = ['', '', '', 'NILAI TERENDAH'];

            foreach ($ledgerData['subjects'] as $subject) {
                $avgLine[] = $ledgerData['statistics']['subject_averages'][$subject->id] ?? '-';
                $maxLine[] = $ledgerData['statistics']['subject_max'][$subject->id] ?? '-';
                $minLine[] = $ledgerData['statistics']['subject_min'][$subject->id] ?? '-';
            }

            $avgLine[] = '';
            $avgLine[] = $ledgerData['statistics']['overall_class_average'];
            $maxLine[] = '';
            $maxLine[] = $ledgerData['statistics']['highest_average'];
            $minLine[] = '';
            $minLine[] = $ledgerData['statistics']['lowest_average'];

            fputcsv($handle, $avgLine);
            fputcsv($handle, $maxLine);
            fputcsv($handle, $minLine);

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function print(Request $request, ClassLedgerService $service): View
    {
        $selectedSemester = Semester::query()->with('academicYear')->findOrFail($request->integer('semester_id'));
        $selectedClass = SchoolClass::query()->findOrFail($request->integer('school_class_id'));
        $sortByRank = $request->boolean('sort_by_rank', false);
        $showRanking = $request->boolean('show_ranking', true);

        $ledgerData = $service->getLedgerData($selectedSemester, $selectedClass, $sortByRank);

        return view('admin.leger.print', [
            'ledgerData' => $ledgerData,
            'showRanking' => $showRanking,
        ]);
    }
}
