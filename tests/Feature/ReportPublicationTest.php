<?php

use App\Models\AcademicYear;
use App\Models\Enrollment;
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

function createReadyDraft(): array
{
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $semester = Semester::factory()->create(['academic_year_id' => $year->id]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id]);
    $student = Student::factory()->create([
        'name' => 'Ahmad Faiz Al-Faruq',
        'nis' => '0101',
        'nisn' => '0081234501',
        'gender' => 'Laki-laki',
        'birth_place' => 'Jakarta',
        'birth_date' => '2015-04-12',
    ]);
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'semester_id' => $semester->id,
        'school_class_id' => $class->id,
    ]);

    $draft = app(ReportDraftBuilder::class)->refresh($student, $semester, $admin);

    return compact('admin', 'year', 'semester', 'class', 'student', 'enrollment', 'draft');
}

it('publishes a ready report draft and creates PDF file archive in storage', function (): void {
    $data = createReadyDraft();

    $response = $this->actingAs($data['admin'])
        ->from("/admin/pengelolaan-rapor/{$data['student']->id}?semester_id={$data['semester']->id}")
        ->post(route('admin.report-publications.store', $data['draft']));

    $response->assertRedirect("/admin/pengelolaan-rapor/{$data['student']->id}?semester_id={$data['semester']->id}");
    $response->assertSessionHas('status', 'Rapor terbit.');

    $publication = ReportPublication::query()->where('report_draft_id', $data['draft']->id)->first();
    expect($publication)->not->toBeNull()
        ->and($publication->student_id)->toBe($data['student']->id)
        ->and($publication->semester_id)->toBe($data['semester']->id)
        ->and($publication->published_by_user_id)->toBe($data['admin']->id)
        ->and(Storage::exists($publication->pdf_path))->toBeTrue();
});

it('handles republication idempotently without duplicate records', function (): void {
    $data = createReadyDraft();

    // First publication
    $this->actingAs($data['admin'])
        ->post(route('admin.report-publications.store', $data['draft']))
        ->assertSessionHas('status', 'Rapor terbit.');

    $countAfterFirst = ReportPublication::query()->where('report_draft_id', $data['draft']->id)->count();
    expect($countAfterFirst)->toBe(1);

    // Second publication attempt
    $this->actingAs($data['admin'])
        ->post(route('admin.report-publications.store', $data['draft']))
        ->assertSessionHas('status', 'Rapor terbit.');

    $countAfterSecond = ReportPublication::query()->where('report_draft_id', $data['draft']->id)->count();
    expect($countAfterSecond)->toBe(1);
});

it('refuses to publish when draft source data has changed (expired draft)', function (): void {
    $data = createReadyDraft();

    // Modify student source data after draft was compiled
    $data['student']->update(['address' => 'Alamat Baru yang Berbeda']);

    $response = $this->actingAs($data['admin'])
        ->from("/admin/pengelolaan-rapor/{$data['student']->id}?semester_id={$data['semester']->id}")
        ->post(route('admin.report-publications.store', $data['draft']));

    $response->assertRedirect("/admin/pengelolaan-rapor/{$data['student']->id}?semester_id={$data['semester']->id}");
    $response->assertSessionHas('error');
    expect(session('error'))->toContain('Rapor belum dapat diterbitkan');

    expect(ReportPublication::query()->where('report_draft_id', $data['draft']->id)->count())->toBe(0);
});

it('refuses to publish an incomplete draft missing student identity', function (): void {
    $admin = User::factory()->admin()->create();
    $year = AcademicYear::factory()->create();
    $semester = Semester::factory()->create(['academic_year_id' => $year->id]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id]);
    $student = Student::factory()->create([
        'name' => 'Siswa Belum Lengkap',
        'nis' => '0202',
        'gender' => 'Laki-laki',
        'birth_place' => null, // missing
        'birth_date' => null,  // missing
    ]);
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'semester_id' => $semester->id,
        'school_class_id' => $class->id,
    ]);

    $draft = app(ReportDraftBuilder::class)->refresh($student, $semester, $admin);

    $response = $this->actingAs($admin)
        ->from("/admin/pengelolaan-rapor/{$student->id}?semester_id={$semester->id}")
        ->post(route('admin.report-publications.store', $draft));

    $response->assertSessionHas('error');
    expect(session('error'))->toContain('Rapor belum dapat diterbitkan')
        ->and(ReportPublication::query()->where('report_draft_id', $draft->id)->count())->toBe(0);
});

it('forbids guardians from publishing reports and downloading publications', function (): void {
    $data = createReadyDraft();
    $wali = User::factory()->wali()->create();

    // Attempt publish as guardian
    $this->actingAs($wali)
        ->post(route('admin.report-publications.store', $data['draft']))
        ->assertForbidden();

    // Create a publication first as admin
    $this->actingAs($data['admin'])
        ->post(route('admin.report-publications.store', $data['draft']));

    $publication = ReportPublication::query()->where('report_draft_id', $data['draft']->id)->firstOrFail();

    // Attempt download as guardian
    $this->actingAs($wali)
        ->get(route('admin.report-publications.download', $publication))
        ->assertForbidden();
});

it('redirects unauthenticated guests to login on publish and download routes', function (): void {
    $data = createReadyDraft();

    $this->post(route('admin.report-publications.store', $data['draft']))
        ->assertRedirect('/login');

    $publication = ReportPublication::factory()->create([
        'report_draft_id' => $data['draft']->id,
        'student_id' => $data['student']->id,
        'semester_id' => $data['semester']->id,
    ]);

    $this->get(route('admin.report-publications.download', $publication))
        ->assertRedirect('/login');
});

it('cleans up the generated PDF file from storage when saving publication fails', function (): void {
    $data = createReadyDraft();

    $deletedPaths = [];
    Storage::shouldReceive('put')->andReturnTrue();
    Storage::shouldReceive('delete')->andReturnUsing(function ($path) use (&$deletedPaths) {
        $deletedPaths[] = $path;

        return true;
    });

    // Make ReportPublication::create throw exception inside transaction
    ReportPublication::creating(function () {
        throw new RuntimeException('Simulated database exception during archive creation');
    });

    try {
        $this->actingAs($data['admin'])
            ->post(route('admin.report-publications.store', $data['draft']));
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Simulated database exception during archive creation');
    }

    expect($deletedPaths)->not->toBeEmpty()
        ->and($deletedPaths[0])->toContain('reports/'.$data['draft']->id.'/final-');
});

it('renders Terbit status, timestamp, and download button on draft detail view when published', function (): void {
    $data = createReadyDraft();

    $this->actingAs($data['admin'])
        ->post(route('admin.report-publications.store', $data['draft']));

    $publication = ReportPublication::query()->where('report_draft_id', $data['draft']->id)->firstOrFail();

    $response = $this->actingAs($data['admin'])
        ->get(route('admin.report-drafts.show', ['student' => $data['student']->id, 'semester_id' => $data['semester']->id]));

    $response->assertOk()
        ->assertSee('Terbit')
        ->assertSee('Waktu terbit:')
        ->assertSee('Unduh PDF')
        ->assertSee(route('admin.report-publications.download', $publication))
        ->assertDontSee('Draf Siap Diterbitkan');
});

it('renders Terbitkan button on draft detail view when ready and unpublished', function (): void {
    $data = createReadyDraft();

    $response = $this->actingAs($data['admin'])
        ->get(route('admin.report-drafts.show', ['student' => $data['student']->id, 'semester_id' => $data['semester']->id]));

    $response->assertOk()
        ->assertSee('Draf Siap Diterbitkan')
        ->assertSee('Terbitkan')
        ->assertDontSee('Waktu terbit:');
});

it('renders checklist explaining unready status when draft has changed', function (): void {
    $data = createReadyDraft();

    // Make source data changed
    $data['student']->update(['name' => 'Nama Yang Telah Diubah']);

    $response = $this->actingAs($data['admin'])
        ->get(route('admin.report-drafts.show', ['student' => $data['student']->id, 'semester_id' => $data['semester']->id]));

    $response->assertOk()
        ->assertSee('Data sumber berubah')
        ->assertDontSee('Draf Siap Diterbitkan');
});

it('allows admin to download published PDF', function (): void {
    $data = createReadyDraft();

    $this->actingAs($data['admin'])
        ->post(route('admin.report-publications.store', $data['draft']));

    $publication = ReportPublication::query()->where('report_draft_id', $data['draft']->id)->firstOrFail();

    $response = $this->actingAs($data['admin'])
        ->get(route('admin.report-publications.download', $publication));

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain("attachment; filename=rapor-{$publication->id}.pdf");
});
