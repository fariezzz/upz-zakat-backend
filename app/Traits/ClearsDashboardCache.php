<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Trait untuk membersihkan semua cache dashboard.
 *
 * Digunakan oleh model-model yang datanya muncul di dashboard
 * (Transaksi, Muzakki, ProgramPenyaluran). Dipanggil otomatis
 * via model event saved/deleted.
 *
 * Cache key grafik dan stats menggunakan parameter tahun,
 * jadi kita clear tahun ini DAN tahun lalu (karena stats
 * menghitung persentase perubahan year-over-year).
 */
trait ClearsDashboardCache
{
    public static function bootClearsDashboardCache(): void
    {
        // saved = fired on both create and update
        static::saved(fn () => static::flushDashboardCache());
        static::deleted(fn () => static::flushDashboardCache());
    }

    /**
     * Hapus semua cache key yang terkait dashboard.
     */
    public static function flushDashboardCache(): void
    {
        $tahun = now()->year;

        // Stats dashboard: cache per tahun + tahun lalu (karena hitung perubahan YoY)
        Cache::forget("dashboard_stats_{$tahun}");
        Cache::forget("dashboard_stats_" . ($tahun - 1));
        Cache::forget("dashboard_stats_all");

        // Ringkasan dana: cache per tahun
        Cache::forget("dashboard_ringkasan_{$tahun}");
        Cache::forget("dashboard_ringkasan_" . ($tahun - 1));
        Cache::forget("dashboard_ringkasan_all");



        // Muzakki stats: clear semua variant
        Cache::forget("muzakki_stats_");
        Cache::forget("muzakki_stats_" . md5(''));
        Cache::forget("muzakki_stats_" . md5('dosen_staf'));
        Cache::forget("muzakki_stats_" . md5('umum'));

        // Legacy keys (backward compatibility)
        Cache::forget("dashboard-stats-{$tahun}");
        Cache::forget("dashboard-grafik-{$tahun}");
        Cache::forget("dashboard-ringkasan-dana-{$tahun}");
        Cache::forget('dashboard-transaksi-terbaru');
        Cache::forget("dashboard-program-aktif-{$tahun}");
    }
}
