<?php

use App\Http\Controllers\Admin\AcademicAssessmentController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AssessmentComponentController;
use App\Http\Controllers\Admin\AssessmentRecapController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AttitudeAssessmentController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ClassLedgerController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\ExtracurricularController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\HomeroomNoteController;
use App\Http\Controllers\Admin\NonAcademicRecapController;
use App\Http\Controllers\Admin\ReportDraftController;
use App\Http\Controllers\Admin\ReportPublicationController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SchoolProfileController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\StudentAchievementController;
use App\Http\Controllers\Admin\StudentActivityController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectAssignmentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SystemStatusController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Wali\ChildController;
use App\Http\Controllers\Wali\WaliReportController;
use App\Http\Controllers\WaliDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    // Tahun Ajaran & Semester
    Route::get('/tahun-ajaran', [AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::post('/tahun-ajaran', [AcademicYearController::class, 'store'])->name('academic-years.store');
    Route::put('/tahun-ajaran/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
    Route::delete('/tahun-ajaran/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');
    Route::post('/tahun-ajaran/{academicYear}/semesters', [SemesterController::class, 'store'])->name('semesters.store');
    Route::post('/semesters/{semester}/activate', [SemesterController::class, 'activate'])->name('semesters.activate');
    Route::post('/semesters/{semester}/lock', [SemesterController::class, 'lock'])->name('semesters.lock');
    Route::post('/semesters/{semester}/unlock', [SemesterController::class, 'unlock'])->name('semesters.unlock');

    // Kelas
    Route::get('/kelas', [SchoolClassController::class, 'index'])->name('school-classes.index');
    Route::post('/kelas', [SchoolClassController::class, 'store'])->name('school-classes.store');
    Route::put('/kelas/{schoolClass}', [SchoolClassController::class, 'update'])->name('school-classes.update');
    Route::delete('/kelas/{schoolClass}', [SchoolClassController::class, 'destroy'])->name('school-classes.destroy');

    // Siswa
    Route::get('/siswa', [StudentController::class, 'index'])->name('students.index');
    Route::get('/siswa/tambah', [StudentController::class, 'create'])->name('students.create');
    Route::post('/siswa', [StudentController::class, 'store'])->name('students.store');
    Route::get('/siswa/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::get('/siswa/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/siswa/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/siswa/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

    // Penempatan Kelas (Enrollments)
    Route::get('/penempatan-kelas', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('/penempatan-kelas', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::put('/penempatan-kelas/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollments.update');
    Route::delete('/penempatan-kelas/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

    // Mata Pelajaran
    Route::get('/mata-pelajaran', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/mata-pelajaran', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/mata-pelajaran/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/mata-pelajaran/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    // Penugasan Mata Pelajaran ke Kelas
    Route::get('/penugasan-mapel', [SubjectAssignmentController::class, 'index'])->name('subject-assignments.index');
    Route::post('/penugasan-mapel', [SubjectAssignmentController::class, 'store'])->name('subject-assignments.store');
    Route::delete('/penugasan-mapel/{subjectAssignment}', [SubjectAssignmentController::class, 'destroy'])->name('subject-assignments.destroy');

    // Akun Wali & Hubungan Anak
    Route::get('/wali', [GuardianController::class, 'index'])->name('guardians.index');
    Route::post('/wali', [GuardianController::class, 'store'])->name('guardians.store');
    Route::post('/wali/{guardian}/students', [GuardianController::class, 'linkStudent'])->name('guardians.link-student');
    Route::delete('/wali/{guardian}/students/{student}', [GuardianController::class, 'unlinkStudent'])->name('guardians.unlink-student');

    // Tahap 4: Pengaturan Komponen Penilaian
    Route::get('/pengaturan-penilaian', [AssessmentComponentController::class, 'index'])->name('assessments.components.index');
    Route::post('/pengaturan-penilaian', [AssessmentComponentController::class, 'store'])->name('assessments.components.store');
    Route::put('/pengaturan-penilaian/{component}', [AssessmentComponentController::class, 'update'])->name('assessments.components.update');
    Route::delete('/pengaturan-penilaian/{component}', [AssessmentComponentController::class, 'destroy'])->name('assessments.components.destroy');
    Route::post('/pengaturan-penilaian/preset', [AssessmentComponentController::class, 'applyPreset'])->name('assessments.components.preset');

    // Tahap 4: Penilaian Akademik Siswa (Input Nilai & Deskripsi)
    Route::get('/penilaian', [AcademicAssessmentController::class, 'index'])->name('assessments.index');
    Route::post('/penilaian', [AcademicAssessmentController::class, 'store'])->name('assessments.store');

    // Tahap 4: Rekap Nilai Akademik
    Route::get('/rekap-nilai', [AssessmentRecapController::class, 'index'])->name('assessments.recap');

    // Tahap 5: Penilaian Nonakademik — Sikap dan Perilaku
    Route::get('/sikap', [AttitudeAssessmentController::class, 'index'])->name('attitudes.index');
    Route::post('/sikap', [AttitudeAssessmentController::class, 'store'])->name('attitudes.store');
    Route::post('/sikap/aspek', [AttitudeAssessmentController::class, 'storeAspect'])->name('attitudes.aspects.store');
    Route::put('/sikap/aspek/{aspect}', [AttitudeAssessmentController::class, 'updateAspect'])->name('attitudes.aspects.update');
    Route::post('/sikap/aspek/standar', [AttitudeAssessmentController::class, 'initializeStandardAspects'])->name('attitudes.aspects.standard');

    // Tahap 5: Penilaian Nonakademik — Kehadiran Siswa
    Route::get('/kehadiran', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('/kehadiran/harian', [AttendanceController::class, 'storeDaily'])->name('attendances.store-daily');

    // Tahap 5: Penilaian Nonakademik — Keaktifan Siswa
    Route::get('/keaktifan', [StudentActivityController::class, 'index'])->name('activities.index');
    Route::post('/keaktifan', [StudentActivityController::class, 'store'])->name('activities.store');
    Route::delete('/keaktifan/{activity}', [StudentActivityController::class, 'destroy'])->name('activities.destroy');

    // Tahap 5: Penilaian Nonakademik — Ekstrakurikuler
    Route::get('/ekstrakurikuler', [ExtracurricularController::class, 'index'])->name('extracurriculars.index');
    Route::post('/ekstrakurikuler/master', [ExtracurricularController::class, 'storeMaster'])->name('extracurriculars.store-master');
    Route::put('/ekstrakurikuler/master/{extracurricular}', [ExtracurricularController::class, 'updateMaster'])->name('extracurriculars.update-master');
    Route::post('/ekstrakurikuler/partisipasi', [ExtracurricularController::class, 'storeParticipation'])->name('extracurriculars.store-participation');
    Route::delete('/ekstrakurikuler/partisipasi/{studentExtracurricular}', [ExtracurricularController::class, 'destroyParticipation'])->name('extracurriculars.destroy-participation');

    // Tahap 5: Penilaian Nonakademik — Prestasi Siswa
    Route::get('/prestasi', [StudentAchievementController::class, 'index'])->name('achievements.index');
    Route::post('/prestasi', [StudentAchievementController::class, 'store'])->name('achievements.store');
    Route::delete('/prestasi/{achievement}', [StudentAchievementController::class, 'destroy'])->name('achievements.destroy');

    // Tahap 5: Penilaian Nonakademik — Catatan Wali Kelas
    Route::get('/catatan-wali-kelas', [HomeroomNoteController::class, 'index'])->name('homeroom-notes.index');
    Route::post('/catatan-wali-kelas', [HomeroomNoteController::class, 'store'])->name('homeroom-notes.store');

    // Tahap 5: Penilaian Nonakademik — Rekap Nonakademik
    Route::get('/rekap-nonakademik', [NonAcademicRecapController::class, 'index'])->name('non-academic-recap.index');
    Route::get('/rekap-nonakademik/{student}', [NonAcademicRecapController::class, 'show'])->name('non-academic-recap.show');
    Route::get('/pengelolaan-rapor', [ReportDraftController::class, 'index'])->name('report-drafts.index');
    Route::get('/profil-madrasah', [SchoolProfileController::class, 'index'])->name('school-profile.index');
    Route::put('/profil-madrasah', [SchoolProfileController::class, 'update'])->name('school-profile.update');
    Route::post('/profil-madrasah/wali-kelas', [SchoolProfileController::class, 'assign'])->name('school-profile.assign');
    Route::get('/pengelolaan-rapor/{student}', [ReportDraftController::class, 'show'])->name('report-drafts.show');
    Route::post('/pengelolaan-rapor/{student}/pratinjau', [ReportDraftController::class, 'refresh'])->name('report-drafts.refresh');
    Route::get('/pengelolaan-rapor/{student}/pratinjau', [ReportDraftController::class, 'preview'])->name('report-drafts.preview');
    Route::post('/rapor/{reportDraft}/terbitkan', [ReportPublicationController::class, 'store'])->name('report-publications.store');
    Route::get('/rapor-terbit/{reportPublication}/unduh', [ReportPublicationController::class, 'download'])->name('report-publications.download');

    // Tahap 9: Leger Nilai & Rekapitulasi Rombel
    Route::get('/leger-nilai', [ClassLedgerController::class, 'index'])->name('leger.index');
    Route::get('/leger-nilai/ekspor', [ClassLedgerController::class, 'export'])->name('leger.export');
    Route::get('/leger-nilai/cetak', [ClassLedgerController::class, 'print'])->name('leger.print');

    // Tahap 10: Manajemen Backup & Status Sistem
    Route::get('/pengaturan/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/pengaturan/backup/database', [BackupController::class, 'createDatabaseBackup'])->name('backup.database');
    Route::post('/pengaturan/backup/reports', [BackupController::class, 'createReportsBackup'])->name('backup.reports');
    Route::get('/pengaturan/backup/{filename}/unduh', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('/pengaturan/backup/{filename}', [BackupController::class, 'destroy'])->name('backup.destroy');

    Route::get('/sistem/status', SystemStatusController::class)->name('system.status');
});

Route::middleware(['auth', 'role:wali'])->prefix('wali')->name('wali.')->group(function (): void {
    Route::get('/', WaliDashboardController::class)->name('dashboard');
    Route::get('/anak/{student}', [ChildController::class, 'show'])->name('children.show');
    Route::get('/rapor/{reportPublication}', [WaliReportController::class, 'show'])->name('reports.show');
    Route::get('/rapor/{reportPublication}/unduh', [WaliReportController::class, 'download'])->name('reports.download');
});
