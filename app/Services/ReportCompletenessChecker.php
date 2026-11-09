<?php

namespace App\Services;

use App\Models\ReportDraft;
use App\Models\Semester;
use App\Models\Student;

class ReportCompletenessChecker
{
    /** @return array{status: string, missing: array<int, string>, source_has_changed: bool} */
    public function check(Student $student, Semester $semester, ?ReportDraft $draft = null): array
    {
        $snapshot = app(ReportDraftBuilder::class)->build($student, $semester);
        $fingerprint = hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR));
        $missing = [];

        foreach (['name', 'nis', 'gender', 'birth_place', 'birth_date'] as $field) {
            if (blank(data_get($snapshot, "student.{$field}"))) {
                $missing[] = "Identitas siswa: {$field}";
            }
        }

        $changed = $draft !== null && $draft->source_fingerprint !== $fingerprint;
        $status = $draft === null ? 'Belum Disusun' : ($changed ? 'Memerlukan Perbaikan' : ($missing === [] ? 'Siap Ditinjau' : 'Belum Lengkap'));

        return compact('status', 'missing', 'changed') + ['source_has_changed' => $changed];
    }
}
