<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SystemHealthService
{
    /**
     * @return array<string, mixed>
     */
    public function checkHealth(): array
    {
        $checks = [];
        $isHealthy = true;
        $warningsCount = 0;

        // 1. Izin Direktori (Directory Permissions)
        $storageWritable = is_writable(storage_path());
        $cacheWritable = is_writable(app()->bootstrapPath('cache'));

        $checks['directories'] = [
            'storage' => [
                'name' => 'storage/',
                'status' => $storageWritable ? 'ok' : 'fail',
                'message' => $storageWritable ? 'Dapat ditulis (Writable)' : 'Tidak dapat ditulis (Permission Denied)',
            ],
            'bootstrap_cache' => [
                'name' => 'bootstrap/cache/',
                'status' => $cacheWritable ? 'ok' : 'fail',
                'message' => $cacheWritable ? 'Dapat ditulis (Writable)' : 'Tidak dapat ditulis (Permission Denied)',
            ],
        ];

        if (! $storageWritable || ! $cacheWritable) {
            $isHealthy = false;
        }

        // 2. Symlink Public Storage
        $symlinkExists = file_exists(public_path('storage'));
        $checks['symlink'] = [
            'name' => 'Storage Symlink (public/storage)',
            'status' => $symlinkExists ? 'ok' : 'warn',
            'message' => $symlinkExists ? 'Tersambung (Linked)' : 'Belum dibuat (Jalankan: php artisan storage:link)',
        ];

        if (! $symlinkExists) {
            $warningsCount++;
        }

        // 3. Ekstensi PHP Wajib
        $requiredExtensions = [
            'pdo' => 'PDO Data Abstraction',
            'mbstring' => 'Multibyte String (Mbstring)',
            'zip' => 'ZipArchive Compression',
            'gd' => 'GD Image Processing',
            'dom' => 'DOM XML Parser',
            'fileinfo' => 'Fileinfo MIME Detection',
        ];

        $extChecks = [];
        foreach ($requiredExtensions as $ext => $label) {
            $loaded = extension_loaded($ext) || ($ext === 'gd' && extension_loaded('imagick'));
            $extChecks[$ext] = [
                'name' => $label,
                'status' => $loaded ? 'ok' : 'fail',
                'message' => $loaded ? 'Terpasang & Aktif' : 'Tidak ditemukan',
            ];

            if (! $loaded) {
                $isHealthy = false;
            }
        }
        $checks['extensions'] = $extChecks;

        // 4. Koneksi Database
        $dbConnected = false;
        $dbMessage = '';
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
            $dbMessage = 'Koneksi Berhasil ('.config('database.default').')';
        } catch (\Throwable $e) {
            $dbConnected = false;
            $dbMessage = 'Gagal Terhubung: '.$e->getMessage();
            $isHealthy = false;
        }

        $checks['database'] = [
            'name' => 'Koneksi Database Utama',
            'status' => $dbConnected ? 'ok' : 'fail',
            'message' => $dbMessage,
        ];

        // 5. Konfigurasi Lingkungan (Environment Config)
        $env = config('app.env');
        $debug = config('app.debug');
        $key = config('app.key');

        $envStatus = 'ok';
        $envMsg = "Lingkungan {$env}, Debug ".($debug ? 'Aktif' : 'Nonaktif');

        if ($env !== 'local' && $debug === true) {
            $envStatus = 'warn';
            $envMsg = 'Peringatan: APP_DEBUG=true aktif pada lingkungan non-lokal (Rentan bocornya informasi kredensial/stacktrace).';
            $warningsCount++;
        }

        $checks['environment'] = [
            'app_env' => $env,
            'app_debug' => $debug,
            'has_key' => ! empty($key),
            'status' => $envStatus,
            'message' => $envMsg,
        ];

        // 6. Ringkasan Sistem
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_os' => PHP_OS,
            'timezone' => config('app.timezone'),
            'app_url' => config('app.url'),
        ];

        return [
            'is_healthy' => $isHealthy,
            'warnings_count' => $warningsCount,
            'overall_status' => $isHealthy ? ($warningsCount > 0 ? 'WARNING' : 'HEALTHY') : 'CRITICAL',
            'checks' => $checks,
            'system_info' => $systemInfo,
        ];
    }
}
