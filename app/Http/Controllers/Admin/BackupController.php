<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index(BackupService $backupService): View
    {
        $backups = $backupService->listBackups();

        return view('admin.backup.index', compact('backups'));
    }

    public function createDatabaseBackup(BackupService $backupService): RedirectResponse
    {
        $path = $backupService->backupDatabase();

        return redirect()->route('admin.backup.index')
            ->with('status', 'Pencadangan database berhasil dibuat ('.basename($path).').');
    }

    public function createReportsBackup(BackupService $backupService): RedirectResponse
    {
        $path = $backupService->backupReports();

        return redirect()->route('admin.backup.index')
            ->with('status', 'Arsip ZIP berkas PDF rapor berhasil dikemas ('.basename($path).').');
    }

    public function download(string $filename, BackupService $backupService): StreamedResponse
    {
        return $backupService->downloadBackup($filename);
    }

    public function destroy(string $filename, BackupService $backupService): RedirectResponse
    {
        $deleted = $backupService->deleteBackup($filename);

        if ($deleted) {
            return redirect()->route('admin.backup.index')
                ->with('status', 'Berkas cadangan '.basename($filename).' berhasil dihapus.');
        }

        return redirect()->route('admin.backup.index')
            ->with('error', 'Berkas cadangan tidak ditemukan atau gagal dihapus.');
    }
}
