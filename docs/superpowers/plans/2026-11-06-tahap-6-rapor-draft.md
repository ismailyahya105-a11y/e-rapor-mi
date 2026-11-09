# Tahap 6 Rapor Draf Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Memberikan admin penyusunan, pemeriksaan, serta pratinjau cetak browser draf rapor snapshot per siswa dan semester.

**Architecture:** `ReportDraftBuilder` menghasilkan payload kanonik dari data sumber historis dan menyimpan/menyegarkan satu `ReportDraft` secara atomik. `ReportCompletenessChecker` menggunakan payload sumber saat ini untuk status/checklist dan fingerprint untuk mendeteksi drift, sedangkan controller hanya memvalidasi konteks lalu menyediakan UI Blade.

**Tech Stack:** Laravel 13.37, PHP 8.4, SQLite, Blade, Tailwind CSS v4, Pest 5.

**Spec:** `docs/superpowers/specs/2026-11-06-tahap-6-rapor-draft-design.md`

## Global Constraints

- Tidak menambah dependensi; tidak menjalankan `migrate:fresh`, `db:wipe`, atau mengubah data sumber.
- Snapshot draf unik pada `student_id` dan `semester_id`, memakai enrollment historis, dan atomik.
- Draf boleh tidak lengkap; hanya status `Siap Ditinjau` yang memerlukan seluruh data wajib.
- Prestasi, ekstrakurikuler, dan keaktifan bersifat opsional; nilai `null` tidak boleh berubah menjadi `0`.
- Semua endpoint Tahap 6 hanya untuk `auth` + `role:admin`; wali tidak dapat mengakses draf.
- Gunakan komponen dashboard dan identitas emerald–gold yang ada; pratinjau mencetak browser A4 tanpa PDF/penerbitan.

## Review Focus

- Penghapusan/penambahan satu catatan sumber harus menandai drift, bukan hanya perubahan kolom yang sudah ada.
- Permintaan dengan siswa benar tetapi tidak terdaftar pada kelas/semester kueri harus ditolak tanpa membangun draf.
- File logo yang berubah/hilang setelah snapshot tidak boleh mengubah atau merusak render snapshot lama.
- Dua POST pembaruan bersamaan tidak boleh membuat draf duplikat atau snapshot campuran.
- Snapshot historis harus tetap dapat dijelaskan bila sumber yang direferensikan kemudian dihapus; constraint harus melindungi integritas draf.

---

### Task 1: Persistensi Profil, Wali Kelas, dan Draf

**Files:**
- Create: `database/migrations/2026_11_06_000000_create_report_draft_tables.php`
- Create: `app/Models/SchoolProfile.php`
- Create: `app/Models/HomeroomTeacherAssignment.php`
- Create: `app/Models/ReportDraft.php`
- Create: `database/factories/SchoolProfileFactory.php`, `HomeroomTeacherAssignmentFactory.php`, `ReportDraftFactory.php`
- Modify: `app/Models/SchoolClass.php`, `app/Models/Semester.php`, `app/Models/Student.php`, `app/Models/Enrollment.php`, `app/Models/User.php`
- Test: `tests/Feature/ReportDraftTest.php`

**Interfaces:**
- Produces `ReportDraft` with `student()`, `semester()`, `enrollment()`, `updatedBy()` and casts `snapshot: array`.
- Produces one-per-semester/class `HomeroomTeacherAssignment` and singleton `SchoolProfile` model APIs.

- [ ] Write failing persistence tests for unique draf siswa+semester, casts JSON snapshot, and assignment wali per kelas+semester.
- [ ] Run `php artisan test --compact tests/Feature/ReportDraftTest.php` and confirm failure because models/tables do not exist.
- [ ] Create migration and models with explicit foreign keys/indexes, `restrictOnDelete` for snapshot references, model casts, typed relations, and factories matching project conventions.
- [ ] Run the narrow feature test and `php artisan migrate:status --no-interaction`; confirm it passes and migration is visible.
- [ ] Run `vendor/bin/pint --dirty --format agent`.

### Task 2: Snapshot Builder, Completeness, and Drift

**Files:**
- Create: `app/Services/ReportDraftBuilder.php`
- Create: `app/Services/ReportCompletenessChecker.php`
- Test: `tests/Feature/ReportDraftTest.php`

**Interfaces:**
- Consumes `ReportDraftBuilder::build(Student $student, Semester $semester): array` and `refresh(Student $student, Semester $semester, User $updatedBy): ReportDraft`.
- Produces `ReportCompletenessChecker::check(Student $student, Semester $semester, ?ReportDraft $draft = null): array` with `status`, `missing`, `source_fingerprint`, and `source_has_changed`.

- [ ] Write failing tests for historical enrollment selection, academic/nonacademic snapshot content, nullable score preservation, missing required fields, optional-section exemption, semester isolation, and addition/deletion-based drift.
- [ ] Run only the named tests and confirm each fails before service implementation.
- [ ] Implement source query construction with eager loading and exact student+semester+historical-class constraints; normalize complete payload then hash it. Use `DB::transaction` plus lock/unique invariant for refresh and never call write APIs on source models.
- [ ] Implement required-field checklist with Indonesian labels and source route names; distinguish `Belum Disusun`, `Belum Lengkap`, `Siap Ditinjau`, and stale `Memerlukan Perbaikan`.
- [ ] Run `php artisan test --compact tests/Feature/ReportDraftTest.php` and `vendor/bin/pint --dirty --format agent`.

### Task 3: Admin Management and Settings Boundaries

**Files:**
- Create: `app/Http/Controllers/Admin/ReportDraftController.php`
- Create: `app/Http/Controllers/Admin/SchoolProfileController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/dashboard.blade.php`
- Create: `resources/views/admin/report-drafts/index.blade.php`, `show.blade.php`
- Create: `resources/views/admin/school-profile/index.blade.php`
- Test: `tests/Feature/ReportDraftTest.php`

**Interfaces:**
- Produces routes `admin.report-drafts.index`, `.show`, `.refresh`, `.preview`, and `admin.school-profile.index`/`.update`.
- `ReportDraftController::refresh(Request $request, Student $student): RedirectResponse` operates only on valid selected semester/class enrollment.

- [ ] Write failing HTTP tests for filters, admin listing/status, out-of-context student rejection, admin-only refresh, draf source immutability, wali forbidden, and profile/teacher input validation.
- [ ] Run the focused test file and verify failures are route/action failures.
- [ ] Implement controllers/routes under existing admin middleware, validate selected semester/class and enrollment server-side, use `validated()` input, storage-safe optional logo upload, and pass preloaded presentation data to Blade.
- [ ] Implement responsive dashboard views with loading/empty/error/success states, visible missing items and drift warning, plus navigation links that reuse existing module names.
- [ ] Run the focused suite and Pint; verify dangerous free text is escaped in render tests.

### Task 4: Snapshot Preview and Browser Print

**Files:**
- Create: `resources/views/admin/report-drafts/preview.blade.php`
- Modify: `resources/css/app.css` only if existing utility styling cannot express print page rules
- Test: `tests/Feature/ReportDraftTest.php`

**Interfaces:**
- `ReportDraftController::preview(Request $request, Student $student): View` renders only `$reportDraft->snapshot`, `$check`, and route-safe context.

- [ ] Write failing render tests proving snapshot text appears, later profile/value mutations do not alter preview, stale/incomplete banner appears, and wali receives 403.
- [ ] Run the named preview tests and observe failure before adding view/action behavior.
- [ ] Add formal A4 Blade document: page 1 identities; page 2 academic table/descriptions; page 3 attitude/extracurricular; page 4 attendance/prestasi/catatan; automatic page breaking, buttons, and print-only page numbering. Render reasonable empty optional states.
- [ ] Run preview tests and `npm run build`; correct template/utility issues until both pass.

### Task 5: Documentation, Regression Verification, and Handoff

**Files:**
- Modify: `README.md`, `docs/PRD.md`, `docs/PROGRESS.md`
- Test: `tests/Feature/ReportDraftTest.php`

- [ ] Write/complete feature tests covering changing logo/profile after snapshot, two semesters, targeted refresh only, source row counts/content unchanged, all readiness states, missing data, and unavailable guardian access.
- [ ] Run `php artisan test --compact tests/Feature/ReportDraftTest.php` after each test change and retain only behavior-distinct assertions.
- [ ] Document routes, draf workflow, checklist status, snapshot/drift behavior, exclusions from Tahap 6, schema additions, and actual verification output.
- [ ] Run `php artisan test --compact`, `vendor/bin/pint --dirty --format agent`, and `npm run build`; record actual output in `docs/PROGRESS.md`.

## Self-Review

- Snapshot, settings, class/semester wali, atomic refresh, drift, all UI views, print, and documentation map to Tasks 1–5.
- Required identity, optional nonacademic sections, score null/zero, stale data, access control, manipulation, and source immutability have dedicated tests in Tasks 2–5.
- Service interfaces and route names introduced in Tasks 2–3 are consistently used by Tasks 3–4.
