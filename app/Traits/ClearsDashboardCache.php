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

        // Stats: cache per tahun + tahun lalu (karena hitung perubahan YoY)
        Cache::forget("dashboard-stats-{$tahun}");
        Cache::forget("dashboard-stats-" . ($tahun - 1));

        // Grafik: cache per tahun
        Cache::forget("dashboard-grafik-{$tahun}");
        Cache::forget("dashboard-grafik-" . ($tahun - 1));

        // Ringkasan dana: cache per tahun
        Cache::forget("dashboard-ringkasan-dana-{$tahun}");
        Cache::forget("dashboard-ringkasan-dana-" . ($tahun - 1));

        // Transaksi terbaru: satu key global (tidak per tahun)
        Cache::forget('dashboard-transaksi-terbaru');

        // Program aktif: cache per tahun
        Cache::forget("dashboard-program-aktif-{$tahun}");
    }
}
