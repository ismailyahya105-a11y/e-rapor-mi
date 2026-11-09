<?php

namespace App\Console\Commands;

use App\Services\SystemHealthService;
use Illuminate\Console\Command;

class SystemHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa kesehatan sistem, izin direktori, dan kesiapan deployment ke server produksi';

    /**
     * Execute the console command.
     */
    public function handle(SystemHealthService $service): int
    {
        $this->info('=== AUDIT KESEHATAN SISTEM RAPORTKU ===');
        $this->newLine();

        $result = $service->checkHealth();

        // 1. Direktori
        $this->comment('1. Izin Tulis Direktori (Storage & Cache):');
        foreach ($result['checks']['directories'] as $dir) {
            $status = $dir['status'] === 'ok' ? ' <fg=green;options=bold>[OK]</> ' : ' <fg=red;options=bold>[FAIL]</> ';
            $this->line($status.$dir['name'].' — '.$dir['message']);
        }
        $this->newLine();

        // 2. Storage Symlink
        $this->comment('2. Public Storage Symlink:');
        $symlink = $result['checks']['symlink'];
        $symStatus = $symlink['status'] === 'ok' ? ' <fg=green;options=bold>[OK]</> ' : ' <fg=yellow;options=bold>[WARN]</> ';
        $this->line($symStatus.$symlink['name'].' — '.$symlink['message']);
        $this->newLine();

        // 3. Ekstensi PHP
        $this->comment('3. Ekstensi PHP Wajib:');
        foreach ($result['checks']['extensions'] as $ext) {
            $status = $ext['status'] === 'ok' ? ' <fg=green;options=bold>[OK]</> ' : ' <fg=red;options=bold>[FAIL]</> ';
            $this->line($status.$ext['name'].' — '.$ext['message']);
        }
        $this->newLine();

        // 4. Koneksi Database
        $this->comment('4. Database:');
        $db = $result['checks']['database'];
        $dbStatus = $db['status'] === 'ok' ? ' <fg=green;options=bold>[OK]</> ' : ' <fg=red;options=bold>[FAIL]</> ';
        $this->line($dbStatus.$db['name'].' — '.$db['message']);
        $this->newLine();

        // 5. Konfigurasi Lingkungan
        $this->comment('5. Lingkungan & Konfigurasi (.env):');
        $env = $result['checks']['environment'];
        $envStatus = $env['status'] === 'ok' ? ' <fg=green;options=bold>[OK]</> ' : ' <fg=yellow;options=bold>[WARN]</> ';
        $this->line($envStatus.'APP_ENV: '.$env['app_env'].' | APP_DEBUG: '.($env['app_debug'] ? 'true' : 'false').' — '.$env['message']);
        $this->newLine();

        // Ringkasan Akhir
        if ($result['overall_status'] === 'HEALTHY') {
            $this->info('STATUS AKHIR: [HEALTHY] — Semua komponen siap dan memenuhi syarat produksi!');

            return Command::SUCCESS;
        } elseif ($result['overall_status'] === 'WARNING') {
            $this->warn('STATUS AKHIR: [WARNING] — Sistem dapat berjalan dengan beberapa catatan penyesuaian.');

            return Command::SUCCESS;
        } else {
            $this->error('STATUS AKHIR: [CRITICAL] — Ditemukan kegagalan kritis yang harus diperbaiki sebelum produksi!');

            return Command::FAILURE;
        }
    }
}
