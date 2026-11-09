<?php

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\GuardianStudent;
use App\Models\ReportAccessLog;
use App\Models\ReportPublication;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Services\ReportDraftBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
});

function setupPublishedReportForGuardian(): array
{
    $admin = User::factory()->admin()->create();
    $wali = User::factory()->wali()->create(['name' => 'Bapak Hendra']);
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $semester = Semester::factory()->create(['academic_year_id' => $year->id, 'is_active' => true]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id]);
    $student = Student::factory()->create([
        'name' => 'Ahmad Faiz Al-Faruq',
        'nis' => '0101',
        'nisn' => '0081234501',
        'gender' => 'Laki-laki',
        'birth_place' => 'Jakarta',
        'birth_date' => '2015-04-12',
    ]);

    // Tautkan wali ke siswa
    GuardianStudent::factory()->create([
        'user_id' => $wali->id,
        'student_id' => $student->id,
        'relationship' => 'Ayah',
    ]);

    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'semester_id' => $semester->id,
        'school_class_id' => $class->id,
    ]);

    $draft = app(ReportDraftBuilder::class)->refresh($student, $semester, $admin);

    $pdfPath = 'reports/'.$draft->id.'/final-sample.pdf';
    Storage::put($pdfPath, '%PDF-1.4 dummy content');

    $publication = ReportPublication::factory()->create([
        'report_draft_id' => $draft->id,
        'student_id' => $student->id,
        'semester_id' => $semester->id,
        'published_by_user_id' => $admin->id,
        'snapshot' => $draft->snapshot,
        'pdf_path' => $pdfPath,
        'published_at' => now(),
    ]);

    return compact('admin', 'wali', 'year', 'semester', 'class', 'student', 'enrollment', 'draft', 'publication');
}

it('allows linked guardian to view published report details and logs view action', function (): void {
    $data = setupPublishedReportForGuardian();

    $response = $this->actingAs($data['wali'])
        ->get(route('wali.reports.show', $data['publication']));

    $response->assertOk()
        ->assertSee('Buku Rapor — '.$data['student']->name)
        ->assertSee('Terbit Resmi')
        ->assertSee(route('wali.reports.download', $data['publication']));

    // Assert audit log for 'view' action was created
    expect(ReportAccessLog::query()
        ->where('report_publication_id', $data['publication']->id)
        ->where('user_id', $data['wali']->id)
        ->where('action', 'view')
        ->exists()
    )->toBeTrue();
});

it('allows linked guardian to download published report PDF and logs download audit log', function (): void {
    $data = setupPublishedReportForGuardian();

    $response = $this->actingAs($data['wali'])
        ->get(route('wali.reports.download', $data['publication']));

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('attachment;');

    // Assert audit log for 'download' action was created
    $log = ReportAccessLog::query()
        ->where('report_publication_id', $data['publication']->id)
        ->where('user_id', $data['wali']->id)
        ->where('action', 'download')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->publication_id)->toBe($data['publication']->id)
        ->and($log->downloaded_at)->not->toBeNull();
});

it('forbids guardian from viewing or downloading report of another student', function (): void {
    $data = setupPublishedReportForGuardian();

    // Create another unrelated guardian
    $otherWali = User::factory()->wali()->create(['name' => 'Wali Siswa Lain']);

    // Attempt to view report of studentFaiz
    $this->actingAs($otherWali)
        ->get(route('wali.reports.show', $data['publication']))
        ->assertForbidden();

    // Attempt to download report of studentFaiz
    $this->actingAs($otherWali)
        ->get(route('wali.reports.download', $data['publication']))
        ->assertForbidden();

    // Verify NO audit log was recorded for otherWali
    expect(ReportAccessLog::query()->where('user_id', $otherWali->id)->count())->toBe(0);
});

it('forbids guardian from accessing draft views or admin preview', function (): void {
    $data = setupPublishedReportForGuardian();

    $this->actingAs($data['wali'])
        ->get(route('admin.report-drafts.show', ['student' => $data['student']->id, 'semester_id' => $data['semester']->id]))
        ->assertForbidden();

    $this->actingAs($data['wali'])
        ->get(route('admin.report-drafts.preview', ['student' => $data['student']->id, 'semester_id' => $data['semester']->id]))
        ->assertForbidden();

    $this->actingAs($data['wali'])
        ->post(route('admin.report-publications.store', $data['draft']))
        ->assertForbidden();
});

it('redirects unauthenticated guests to login on guardian report routes', function (): void {
    $data = setupPublishedReportForGuardian();

    $this->get(route('wali.reports.show', $data['publication']))
        ->assertRedirect('/login');

    $this->get(route('wali.reports.download', $data['publication']))
        ->assertRedirect('/login');
});

it('displays published report status on guardian dashboard and child detail page', function (): void {
    $data = setupPublishedReportForGuardian();

    // 1. Guardian Dashboard
    $this->actingAs($data['wali'])
        ->get(route('wali.dashboard'))
        ->assertOk()
        ->assertSee('Buku Rapor Siswa')
        ->assertSee('Terbit')
        ->assertSee(route('wali.reports.show', $data['publication']))
        ->assertSee(route('wali.reports.download', $data['publication']));

    // 2. Child Detail Page
    $this->actingAs($data['wali'])
        ->get(route('wali.children.show', $data['student']))
        ->assertOk()
        ->assertSee('Buku Rapor Resmi Terbit')
        ->assertSee('1 Rapor Tersedia')
        ->assertSee(route('wali.reports.show', $data['publication']))
        ->assertSee(route('wali.reports.download', $data['publication']));
});

it('displays unreleased report status when no publication exists yet', function (): void {
    $wali = User::factory()->wali()->create();
    $student = Student::factory()->create(['name' => 'Siswa Belum Ada Rapor']);
    GuardianStudent::factory()->create([
        'user_id' => $wali->id,
        'student_id' => $student->id,
    ]);

    // Dashboard
    $this->actingAs($wali)
        ->get(route('wali.dashboard'))
        ->assertOk()
        ->assertSee('Belum Terbit')
        ->assertSee('Rapor belum diterbitkan oleh pihak madrasah');

    // Child detail
    $this->actingAs($wali)
        ->get(route('wali.children.show', $student))
        ->assertOk()
        ->assertSee('Belum ada buku rapor resmi yang diterbitkan');
});

it('displays audit log tracking status on admin draft detail page', function (): void {
    $data = setupPublishedReportForGuardian();

    // 1. Before download by guardian: admin sees "Belum diunduh"
    $responseBefore = $this->actingAs($data['admin'])
        ->get(route('admin.report-drafts.show', ['student' => $data['student']->id, 'semester_id' => $data['semester']->id]));

    $responseBefore->assertOk()
        ->assertSee('Pelacakan Akses Wali Murid')
        ->assertSee('Belum diunduh');

    // 2. Guardian downloads the report
    $this->actingAs($data['wali'])
        ->get(route('wali.reports.download', $data['publication']));

    // 3. After download by guardian: admin sees "Sudah diunduh oleh wali murid pada"
    $responseAfter = $this->actingAs($data['admin'])
        ->get(route('admin.report-drafts.show', ['student' => $data['student']->id, 'semester_id' => $data['semester']->id]));

    $responseAfter->assertOk()
        ->assertSee('Pelacakan Akses Wali Murid')
        ->assertSee('Sudah diunduh oleh wali murid pada')
        ->assertSee('Bapak Hendra');
});
