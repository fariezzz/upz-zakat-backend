<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Muzakki;
use App\Models\ProgramPenyaluran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/stats
     * Statistik utama: total dana terkumpul, disalurkan, saldo, jumlah muzakki
     *
     * Cache TTL: 5 menit.
     * Stats adalah data agregat yang tidak berubah tiap detik — 5 menit cukup
     * untuk mengurangi cold-start Neon tanpa mengorbankan keakuratan signifikan.
     * Cache key menyertakan tahun karena response berbeda per tahun.
     */
    public function stats(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);

        $data = Cache::remember("dashboard-stats-{$tahun}", now()->addMinutes(5), function () use ($tahun) {
            $tahunLalu = $tahun - 1;

            // Dana terkumpul tahun ini (semua transaksi masuk)
            $terkumpulTahunIni = Transaksi::where('jenis', 'masuk')
                ->where('tahun', $tahun)
                ->sum('nominal');

            // Dana terkumpul tahun lalu (buat persentase perubahan)
            $terkumpulTahunLalu = Transaksi::where('jenis', 'masuk')
                ->where('tahun', $tahunLalu)
                ->sum('nominal');

            // Dana disalurkan tahun ini
            $disalurkanTahunIni = Transaksi::where('jenis', 'keluar')
                ->where('tahun', $tahun)
                ->sum('nominal');

            // Dana disalurkan tahun lalu
            $disalurkanTahunLalu = Transaksi::where('jenis', 'keluar')
                ->where('tahun', $tahunLalu)
                ->sum('nominal');

            // Saldo = selisih semua transaksi masuk - semua transaksi keluar (overall)
            $totalMasuk   = Transaksi::where('jenis', 'masuk')->sum('nominal');
            $totalKeluar  = Transaksi::where('jenis', 'keluar')->sum('nominal');
            $saldoKasBank = $totalMasuk - $totalKeluar;

            // Jumlah muzakki aktif tahun ini (punya transaksi di tahun ini)
            $muzakkiTahunIni = Muzakki::where('status', 'aktif')
                ->whereHas('transaksi', fn($q) => $q->where('tahun', $tahun))
                ->count();

            $muzakkiTahunLalu = Muzakki::where('status', 'aktif')
                ->whereHas('transaksi', fn($q) => $q->where('tahun', $tahunLalu))
                ->count();

            // Hitung persentase perubahan
            $perubDanaTerkumpul  = $this->hitungPerubahan($terkumpulTahunIni, $terkumpulTahunLalu);
            $perubDanaDisalurkan = $this->hitungPerubahan($disalurkanTahunIni, $disalurkanTahunLalu);
            $perubMuzakki        = $this->hitungPerubahan($muzakkiTahunIni, $muzakkiTahunLalu);

            return [
                'totalDanaTerkumpul'      => (int) $terkumpulTahunIni,
                'totalDanaDisalurkan'     => (int) $disalurkanTahunIni,
                'saldoKasBank'            => (int) $saldoKasBank,
                'totalMuzakki'            => (int) $muzakkiTahunIni,
                'perubahanDanaTerkumpul'  => $perubDanaTerkumpul,
                'perubahanDanaDisalurkan' => $perubDanaDisalurkan,
                'perubahanMuzakki'        => $perubMuzakki,
            ];
        });

        return response()->json($data);
    }

    /**
     * GET /api/dashboard/ringkasan-dana
     * Data untuk donut chart: breakdown zakat, infaq, sedekah, lainnya
     *
     * Cache TTL: 10 menit.
     * Breakdown per kategori berubah lebih jarang dari stats (perlu transaksi
     * dengan kategori berbeda untuk mengubah distribusi), jadi TTL lebih lama.
     */
    public function ringkasanDana(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);

        $data = Cache::remember("dashboard-ringkasan-dana-{$tahun}", now()->addMinutes(10), function () use ($tahun) {
            $kategoriColors = [
                'Zakat'       => '#2e7d38',
                'Infaq'       => '#3b82f6',
                'Sedekah'     => '#eab308',
                'Dana Lainnya' => '#a855f7',
            ];

            $rows = Transaksi::where('jenis', 'masuk')
                ->where('tahun', $tahun)
                ->whereIn('kategori', array_keys($kategoriColors))
                ->select('kategori', DB::raw('SUM(nominal) as total'))
                ->groupBy('kategori')
                ->get();

            $grandTotal = $rows->sum('total');

            return $rows->map(function ($row) use ($kategoriColors, $grandTotal) {
                $percent = $grandTotal > 0
                    ? round(($row->total / $grandTotal) * 100, 1)
                    : 0;

                return [
                    'name'    => $row->kategori,
                    'value'   => (int) $row->total,
                    'percent' => $percent,
                    'color'   => $kategoriColors[$row->kategori] ?? '#94a3b8',
                ];
            })->values()->toArray();
        });

        return response()->json($data);
    }

    /**
     * GET /api/dashboard/grafik?tahun=2025
     * Data grafik line chart pengumpulan vs penyaluran per bulan
     *
     * Cache TTL: 15 menit.
     * Data historis per bulan berubah sangat jarang — hanya saat ada transaksi
     * baru di bulan/tahun tersebut. 15 menit aman untuk mengurangi query berat
     * (2x GROUP BY per request). Cache key include tahun.
     */
    public function grafik(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);

        $data = Cache::remember("dashboard-grafik-{$tahun}", now()->addMinutes(15), function () use ($tahun) {
            $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

            // Ambil semua data bulan sekaligus, pivot manual
            $masuk = Transaksi::where('jenis', 'masuk')
                ->where('tahun', $tahun)
                ->select('bulan', DB::raw('SUM(nominal) as total'))
                ->groupBy('bulan')
                ->pluck('total', 'bulan');

            $keluar = Transaksi::where('jenis', 'keluar')
                ->where('tahun', $tahun)
                ->select('bulan', DB::raw('SUM(nominal) as total'))
                ->groupBy('bulan')
                ->pluck('total', 'bulan');

            $data = [];
            for ($b = 1; $b <= 12; $b++) {
                $data[] = [
                    'bulan'        => $namaBulan[$b - 1],
                    'pengumpulan'  => (int) ($masuk[$b] ?? 0),
                    'penyaluran'   => (int) ($keluar[$b] ?? 0),
                ];
            }

            return $data;
        });

        return response()->json($data);
    }

    /**
     * GET /api/transaksi?limit=5&sort=terbaru
     * Daftar transaksi terbaru
     *
     * Cache TTL: 2 menit.
     * Transaksi terbaru adalah data yang user harapkan paling up-to-date,
     * tapi query-nya sederhana (ORDER BY + LIMIT, tanpa agregasi). 2 menit
     * dipilih sebagai kompromi: cukup untuk menghindari cold-start Neon
     * tapi tetap responsif terhadap transaksi baru.
     *
     * Catatan: cache key tidak include parameter limit karena dashboard
     * selalu memanggil dengan limit=5. Jika nanti ada kebutuhan limit
     * berbeda, bisa ditambahkan ke cache key.
     */
    public function transaksiTerbaru(Request $request)
    {
        $limit = (int) $request->query('limit', 5);
        $limit = min($limit, 50); // batasi max 50

        $data = Cache::remember('dashboard-transaksi-terbaru', now()->addMinutes(2), function () use ($limit) {
            return Transaksi::orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($trx) {
                    return [
                        'id'        => $trx->kode,
                        'jenis'     => $trx->jenis,
                        'kategori'  => $trx->kategori,
                        'deskripsi' => $trx->deskripsi,
                        'nominal'   => (int) $trx->nominal,
                        'waktu'     => $trx->created_at->toIso8601String(),
                    ];
                })
                ->toArray();
        });

        return response()->json($data);
    }

    /**
     * GET /api/program?status=aktif
     * Daftar program penyaluran aktif
     *
     * Cache TTL: 5 menit.
     * Program penyaluran jarang berubah (hanya saat admin update progress
     * atau tambah program baru). 5 menit sudah sangat responsif untuk
     * data yang sifatnya semi-statis ini.
     */
    public function programAktif(Request $request)
    {
        $status = $request->query('status', 'aktif');
        $tahun  = $request->query('tahun', now()->year);

        $data = Cache::remember("dashboard-program-aktif-{$tahun}", now()->addMinutes(5), function () use ($status, $tahun) {
            return ProgramPenyaluran::where('status', $status)
                ->where('tahun', $tahun)
                ->get()
                ->map(function ($prog) {
                    return [
                        'id'       => $prog->kode,
                        'nama'     => $prog->nama,
                        'penerima' => (int) $prog->jumlah_penerima,
                        'nominal'  => (int) $prog->nominal_disalurkan,
                        'progress' => $prog->progress,
                    ];
                })
                ->toArray();
        });

        return response()->json($data);
    }

    /**
     * GET /api/dashboard/all?tahun=2025
     * Gabungan semua data dashboard dalam 1 request.
     *
     * Menghilangkan overhead 5x Sanctum token verification (5 round-trip
     * ke Neon untuk cek personal_access_tokens) menjadi hanya 1x.
     * Setiap bagian tetap di-cache individu sehingga invalidation
     * per-bagian tetap bekerja.
     */
    public function all(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);

        // Ambil dari cache individual — jika sudah ada, langsung return tanpa query
        $stats = Cache::remember("dashboard-stats-{$tahun}", now()->addMinutes(5), function () use ($tahun) {
            return $this->buildStats($tahun);
        });

        $ringkasanDana = Cache::remember("dashboard-ringkasan-dana-{$tahun}", now()->addMinutes(10), function () use ($tahun) {
            return $this->buildRingkasanDana($tahun);
        });

        $grafik = Cache::remember("dashboard-grafik-{$tahun}", now()->addMinutes(15), function () use ($tahun) {
            return $this->buildGrafik($tahun);
        });

        $transaksi = Cache::remember('dashboard-transaksi-terbaru', now()->addMinutes(2), function () {
            return $this->buildTransaksiTerbaru(5);
        });

        $program = Cache::remember("dashboard-program-aktif-{$tahun}", now()->addMinutes(5), function () use ($tahun) {
            return $this->buildProgramAktif('aktif', $tahun);
        });

        return response()->json([
            'stats'         => $stats,
            'ringkasanDana' => $ringkasanDana,
            'grafik'        => $grafik,
            'transaksi'     => $transaksi,
            'program'       => $program,
        ]);
    }

    // ——————————————————————————————————
    // Builder methods (reusable oleh endpoint individual & endpoint all)
    // ——————————————————————————————————

    private function buildStats(int $tahun): array
    {
        $tahunLalu = $tahun - 1;

        $terkumpulTahunIni  = Transaksi::where('jenis', 'masuk')->where('tahun', $tahun)->sum('nominal');
        $terkumpulTahunLalu = Transaksi::where('jenis', 'masuk')->where('tahun', $tahunLalu)->sum('nominal');
        $disalurkanTahunIni  = Transaksi::where('jenis', 'keluar')->where('tahun', $tahun)->sum('nominal');
        $disalurkanTahunLalu = Transaksi::where('jenis', 'keluar')->where('tahun', $tahunLalu)->sum('nominal');

        $totalMasuk   = Transaksi::where('jenis', 'masuk')->sum('nominal');
        $totalKeluar  = Transaksi::where('jenis', 'keluar')->sum('nominal');
        $saldoKasBank = $totalMasuk - $totalKeluar;

        $muzakkiTahunIni  = Muzakki::where('status', 'aktif')
            ->whereHas('transaksi', fn($q) => $q->where('tahun', $tahun))->count();
        $muzakkiTahunLalu = Muzakki::where('status', 'aktif')
            ->whereHas('transaksi', fn($q) => $q->where('tahun', $tahunLalu))->count();

        return [
            'totalDanaTerkumpul'      => (int) $terkumpulTahunIni,
            'totalDanaDisalurkan'     => (int) $disalurkanTahunIni,
            'saldoKasBank'            => (int) $saldoKasBank,
            'totalMuzakki'            => (int) $muzakkiTahunIni,
            'perubahanDanaTerkumpul'  => $this->hitungPerubahan($terkumpulTahunIni, $terkumpulTahunLalu),
            'perubahanDanaDisalurkan' => $this->hitungPerubahan($disalurkanTahunIni, $disalurkanTahunLalu),
            'perubahanMuzakki'        => $this->hitungPerubahan($muzakkiTahunIni, $muzakkiTahunLalu),
        ];
    }

    private function buildRingkasanDana(int $tahun): array
    {
        $kategoriColors = [
            'Zakat'        => '#2e7d38',
            'Infaq'        => '#3b82f6',
            'Sedekah'      => '#eab308',
            'Dana Lainnya' => '#a855f7',
        ];

        $rows = Transaksi::where('jenis', 'masuk')
            ->where('tahun', $tahun)
            ->whereIn('kategori', array_keys($kategoriColors))
            ->select('kategori', DB::raw('SUM(nominal) as total'))
            ->groupBy('kategori')
            ->get();

        $grandTotal = $rows->sum('total');

        return $rows->map(function ($row) use ($kategoriColors, $grandTotal) {
            return [
                'name'    => $row->kategori,
                'value'   => (int) $row->total,
                'percent' => $grandTotal > 0 ? round(($row->total / $grandTotal) * 100, 1) : 0,
                'color'   => $kategoriColors[$row->kategori] ?? '#94a3b8',
            ];
        })->values()->toArray();
    }

    private function buildGrafik(int $tahun): array
    {
        $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        $masuk = Transaksi::where('jenis', 'masuk')->where('tahun', $tahun)
            ->select('bulan', DB::raw('SUM(nominal) as total'))
            ->groupBy('bulan')->pluck('total', 'bulan');

        $keluar = Transaksi::where('jenis', 'keluar')->where('tahun', $tahun)
            ->select('bulan', DB::raw('SUM(nominal) as total'))
            ->groupBy('bulan')->pluck('total', 'bulan');

        $data = [];
        for ($b = 1; $b <= 12; $b++) {
            $data[] = [
                'bulan'       => $namaBulan[$b - 1],
                'pengumpulan' => (int) ($masuk[$b] ?? 0),
                'penyaluran'  => (int) ($keluar[$b] ?? 0),
            ];
        }
        return $data;
    }

    private function buildTransaksiTerbaru(int $limit): array
    {
        return Transaksi::orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($trx) => [
                'id'        => $trx->kode,
                'jenis'     => $trx->jenis,
                'kategori'  => $trx->kategori,
                'deskripsi' => $trx->deskripsi,
                'nominal'   => (int) $trx->nominal,
                'waktu'     => $trx->created_at->toIso8601String(),
            ])
            ->toArray();
    }

    private function buildProgramAktif(string $status, int $tahun): array
    {
        return ProgramPenyaluran::where('status', $status)
            ->where('tahun', $tahun)
            ->get()
            ->map(fn($prog) => [
                'id'       => $prog->kode,
                'nama'     => $prog->nama,
                'penerima' => (int) $prog->jumlah_penerima,
                'nominal'  => (int) $prog->nominal_disalurkan,
                'progress' => $prog->progress,
            ])
            ->toArray();
    }

    // ——————————————————————————————————
    // Helper
    // ——————————————————————————————————

    private function hitungPerubahan(float $sekarang, float $lalu): float
    {
        if ($lalu == 0) return $sekarang > 0 ? 100.0 : 0.0;
        return round((($sekarang - $lalu) / $lalu) * 100, 1);
    }
}

