<?php

use App\Models\ReportPublication;
use App\Models\SchoolProfile;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
});

it('allows admin to create a database backup snapshot', function (): void {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->post(route('admin.backup.database'));

    $response->assertRedirect(route('admin.backup.index'));
    $response->assertSessionHas('status');

    $files = Storage::disk('local')->files('backups');
    expect($files)->not->toBeEmpty();

    $dbBackup = collect($files)->first(fn ($f) => str_contains($f, 'backup-database-'));
    expect($dbBackup)->not->toBeNull();
});

it('allows admin to create a reports ZIP archive backup', function (): void {
    $admin = User::factory()->admin()->create();

    // Create a dummy report file in storage
    Storage::disk('local')->put('reports/1/final-sample.pdf', '%PDF-1.4 sample content');

    $response = $this->actingAs($admin)
        ->post(route('admin.backup.reports'));

    $response->assertRedirect(route('admin.backup.index'));
    $response->assertSessionHas('status');

    $files = Storage::disk('local')->files('backups');
    $zipBackup = collect($files)->first(fn ($f) => str_contains($f, 'backup-reports-') && str_ends_with($f, '.zip'));
    expect($zipBackup)->not->toBeNull();
});

it('allows admin to list and download backup files', function (): void {
    $admin = User::factory()->admin()->create();

    // Buat file cadangan tiruan
    Storage::disk('local')->put('backups/backup-database-2026-11-09-120000.sqlite', 'SQLite format 3 dummy');

    // 1. List
    $responseList = $this->actingAs($admin)
        ->get(route('admin.backup.index'));

    $responseList->assertOk()
        ->assertSee('backup-database-2026-11-09-120000.sqlite')
        ->assertSee('Database Sistem');

    // 2. Download
    $responseDownload = $this->actingAs($admin)
        ->get(route('admin.backup.download', ['filename' => 'backup-database-2026-11-09-120000.sqlite']));

    $responseDownload->assertOk();
    expect($responseDownload->headers->get('Content-Disposition'))->toContain('attachment; filename=backup-database-2026-11-09-120000.sqlite');
});

it('allows admin to safely delete a backup file', function (): void {
    $admin = User::factory()->admin()->create();

    $filename = 'backup-database-2026-11-09-120000.sqlite';
    Storage::disk('local')->put("backups/{$filename}", 'Dummy content');
    expect(Storage::disk('local')->exists("backups/{$filename}"))->toBeTrue();

    $response = $this->actingAs($admin)
        ->delete(route('admin.backup.destroy', ['filename' => $filename]));

    $response->assertRedirect(route('admin.backup.index'));
    $response->assertSessionHas('status');

    expect(Storage::disk('local')->exists("backups/{$filename}"))->toBeFalse();
});

it('forbids guardians from accessing backup management and download routes', function (): void {
    $wali = User::factory()->wali()->create();
    $filename = 'backup-sample.zip';
    Storage::disk('local')->put("backups/{$filename}", 'Dummy content');

    $this->actingAs($wali)->get(route('admin.backup.index'))->assertForbidden();
    $this->actingAs($wali)->post(route('admin.backup.database'))->assertForbidden();
    $this->actingAs($wali)->post(route('admin.backup.reports'))->assertForbidden();
    $this->actingAs($wali)->get(route('admin.backup.download', $filename))->assertForbidden();
    $this->actingAs($wali)->delete(route('admin.backup.destroy', $filename))->assertForbidden();
});

it('redirects unauthenticated guests to login on backup routes', function (): void {
    $this->get(route('admin.backup.index'))->assertRedirect('/login');
    $this->post(route('admin.backup.database'))->assertRedirect('/login');
    $this->post(route('admin.backup.reports'))->assertRedirect('/login');
});

it('renders system health check web status and artisan command', function (): void {
    $admin = User::factory()->admin()->create();
    $wali = User::factory()->wali()->create();

    // Admin view
    $response = $this->actingAs($admin)
        ->get(route('admin.system.status'));

    $response->assertOk()
        ->assertSee('Status Sistem')
        ->assertSee('Kesiapan Produksi')
        ->assertSee('Izin Tulis Direktori')
        ->assertSee('Ekstensi PHP Wajib')
        ->assertSee('Audit Konfigurasi Lingkungan');

    // Guardian view forbidden
    $this->actingAs($wali)
        ->get(route('admin.system.status'))
        ->assertForbidden();

    // Artisan command
    $this->artisan('system:health')
        ->expectsOutputToContain('AUDIT KESEHATAN SISTEM RAPORTKU')
        ->assertExitCode(0);
});

it('runs clean production seeder without creating dummy students or fake grades', function (): void {
    $this->seed(ProductionSeeder::class);

    // Verify Super Admin created
    $admin = User::query()->where('email', 'admin@raportku.id')->first();
    expect($admin)->not->toBeNull()
        ->and($admin->isAdmin())->toBeTrue();

    // Verify Clean School Profile
    $profile = SchoolProfile::query()->where('npsn', '60000001')->first();
    expect($profile)->not->toBeNull();

    // Verify 12 Standard Subjects
    expect(Subject::query()->count())->toBeGreaterThanOrEqual(12);

    // Verify NO dummy student records or demo publications created by ProductionSeeder
    expect(Student::query()->where('name', 'like', '%dummy%')->count())->toBe(0)
        ->and(ReportPublication::query()->count())->toBe(0);
});
