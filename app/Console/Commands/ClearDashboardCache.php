<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearDashboardCache extends Command
{
    protected $signature   = 'dashboard:cache-clear {--year= : Tahun spesifik (default: tahun ini dan tahun lalu)}';
    protected $description = 'Hapus semua cache dashboard (berguna setelah raw SQL / truncate)';

    public function handle(): void
    {
        $year = (int) ($this->option('year') ?? now()->year);

        $keys = [
            "dashboard-stats-{$year}",
            "dashboard-stats-" . ($year - 1),
            "dashboard-grafik-{$year}",
            "dashboard-grafik-" . ($year - 1),
            "dashboard-ringkasan-dana-{$year}",
            "dashboard-ringkasan-dana-" . ($year - 1),
            'dashboard-transaksi-terbaru',
            "dashboard-program-aktif-{$year}",
        ];

        foreach ($keys as $key) {
            $existed = Cache::has($key);
            Cache::forget($key);
            $this->line(($existed ? '  <info>✓</info>' : '  <comment>–</comment>') . " {$key}");
        }

        $this->newLine();
        $this->info('Cache dashboard berhasil dihapus. Refresh browser untuk melihat data terbaru.');
    }
}
