<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\HomeroomTeacherAssignment;
use App\Models\SchoolClass;
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Models\StudentAttendance;
use App\Models\StudentSubjectResult;
use App\Models\SubjectAssignment;

class ClassLedgerService
{
    /**
     * @return array<string, mixed>
     */
    public function getLedgerData(Semester $semester, SchoolClass $schoolClass, bool $sortByRank = false): array
    {
        $school = SchoolProfile::query()->first();

        $teacher = HomeroomTeacherAssignment::query()
            ->where('semester_id', $semester->id)
            ->where('school_class_id', $schoolClass->id)
            ->first();

        // 1. Ambil seluruh penugasan mapel di kelas dan semester ini
        $assignments = SubjectAssignment::query()
            ->where('semester_id', $semester->id)
            ->where('school_class_id', $schoolClass->id)
            ->join('subjects', 'subjects.id', '=', 'subject_assignments.subject_id')
            ->orderBy('subjects.order')
            ->orderBy('subjects.name')
            ->select('subject_assignments.*')
            ->with('subject')
            ->get();

        $subjects = $assignments->pluck('subject');

        // 2. Ambil seluruh siswa terdaftar di rombel kelas ini
        $enrollments = Enrollment::query()
            ->where('semester_id', $semester->id)
            ->where('school_class_id', $schoolClass->id)
            ->with('student')
            ->get();

        $students = $enrollments->pluck('student')->sortBy('name')->values();

        // 3. Ambil data nilai akhir siswa (StudentSubjectResult)
        $assignmentIds = $assignments->pluck('id');
        $subjectIdToAssignmentId = $assignments->pluck('id', 'subject_id');

        $results = StudentSubjectResult::query()
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('subject_assignment_id', $assignmentIds)
            ->get()
            ->groupBy('student_id');

        // 4. Ambil rekap absensi kehadiran siswa pada semester ini
        $attendances = StudentAttendance::query()
            ->whereIn('student_id', $students->pluck('id'))
            ->where('semester_id', $semester->id)
            ->get()
            ->groupBy('student_id');

        // 5. Susun baris leger per siswa
        $rows = [];
        foreach ($students as $student) {
            $studentResults = $results->get($student->id, collect())->keyBy('subject_assignment_id');
            $studentAtts = $attendances->get($student->id, collect());

            $scores = [];
            $totalScore = 0.0;
            $gradedCount = 0;

            foreach ($assignments as $assignment) {
                $result = $studentResults->get($assignment->id);
                $finalScore = $result?->final_score !== null ? (float) $result->final_score : null;
                $scores[$assignment->subject_id] = $finalScore;

                if ($finalScore !== null) {
                    $totalScore += $finalScore;
                    $gradedCount++;
                }
            }

            $averageScore = $gradedCount > 0 ? round($totalScore / $gradedCount, 2) : 0.0;

            $attendanceRecap = [
                'sakit' => $studentAtts->where('status', 'Sakit')->count(),
                'izin' => $studentAtts->where('status', 'Izin')->count(),
                'alpa' => $studentAtts->where('status', 'Alpa')->count(),
                'hadir' => $studentAtts->where('status', 'Hadir')->count(),
            ];

            $rows[] = [
                'student' => $student,
                'scores' => $scores,
                'total_score' => round($totalScore, 1),
                'average_score' => $averageScore,
                'graded_count' => $gradedCount,
                'attendance' => $attendanceRecap,
                'rank' => 1,
            ];
        }

        // 6. Hitung Ranking / Peringkat (Urutkan dari nilai rata-rata & total tertinggi)
        usort($rows, function ($a, $b) {
            if ($b['average_score'] !== $a['average_score']) {
                return $b['average_score'] <=> $a['average_score'];
            }

            return $b['total_score'] <=> $a['total_score'];
        });

        // Tetapkan peringkat
        $currentRank = 1;
        foreach ($rows as $index => &$row) {
            if ($index > 0) {
                $prev = $rows[$index - 1];
                if ($row['average_score'] === $prev['average_score'] && $row['total_score'] === $prev['total_score']) {
                    $row['rank'] = $prev['rank'];
                } else {
                    $row['rank'] = $index + 1;
                }
            } else {
                $row['rank'] = 1;
            }
        }
        unset($row);

        // Jika tidak diurutkan berdasarkan peringkat, kembalikan ke urutan nama siswa
        if (! $sortByRank) {
            usort($rows, fn ($a, $b) => strcasecmp($a['student']->name, $b['student']->name));
        }

        // 7. Hitung Statistik per Mata Pelajaran
        $subjectAverages = [];
        $subjectMax = [];
        $subjectMin = [];

        foreach ($subjects as $subject) {
            $subjectScores = [];
            foreach ($rows as $r) {
                if (isset($r['scores'][$subject->id]) && $r['scores'][$subject->id] !== null) {
                    $subjectScores[] = $r['scores'][$subject->id];
                }
            }

            if (! empty($subjectScores)) {
                $subjectAverages[$subject->id] = round(array_sum($subjectScores) / count($subjectScores), 1);
                $subjectMax[$subject->id] = max($subjectScores);
                $subjectMin[$subject->id] = min($subjectScores);
            } else {
                $subjectAverages[$subject->id] = null;
                $subjectMax[$subject->id] = null;
                $subjectMin[$subject->id] = null;
            }
        }

        $allAverages = array_filter(array_column($rows, 'average_score'), fn ($v) => $v > 0);
        $overallClassAverage = ! empty($allAverages) ? round(array_sum($allAverages) / count($allAverages), 1) : 0.0;
        $highestAverage = ! empty($allAverages) ? max($allAverages) : 0.0;
        $lowestAverage = ! empty($allAverages) ? min($allAverages) : 0.0;

        return [
            'school' => $school,
            'semester' => $semester,
            'schoolClass' => $schoolClass,
            'teacher' => $teacher,
            'subjects' => $subjects,
            'rows' => $rows,
            'statistics' => [
                'subject_averages' => $subjectAverages,
                'subject_max' => $subjectMax,
                'subject_min' => $subjectMin,
                'overall_class_average' => $overallClassAverage,
                'highest_average' => $highestAverage,
                'lowest_average' => $lowestAverage,
            ],
        ];
    }
}
