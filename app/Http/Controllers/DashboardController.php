<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Muzakki;
use App\Models\ProgramPenyaluran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/stats
     * Statistik utama: total dana terkumpul, disalurkan, saldo, jumlah muzakki
     */
    public function stats(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);
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

        return response()->json([
            'totalDanaTerkumpul'      => (int) $terkumpulTahunIni,
            'totalDanaDisalurkan'     => (int) $disalurkanTahunIni,
            'saldoKasBank'            => (int) $saldoKasBank,
            'totalMuzakki'            => (int) $muzakkiTahunIni,
            'perubahanDanaTerkumpul'  => $perubDanaTerkumpul,
            'perubahanDanaDisalurkan' => $perubDanaDisalurkan,
            'perubahanMuzakki'        => $perubMuzakki,
        ]);
    }

    /**
     * GET /api/dashboard/ringkasan-dana
     * Data untuk donut chart: breakdown zakat, infaq, sedekah, lainnya
     */
    public function ringkasanDana(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);

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

        $data = $rows->map(function ($row) use ($kategoriColors, $grandTotal) {
            $percent = $grandTotal > 0
                ? round(($row->total / $grandTotal) * 100, 1)
                : 0;

            return [
                'name'    => $row->kategori,
                'value'   => (int) $row->total,
                'percent' => $percent,
                'color'   => $kategoriColors[$row->kategori] ?? '#94a3b8',
            ];
        });

        return response()->json($data->values());
    }

    /**
     * GET /api/dashboard/grafik?tahun=2025
     * Data grafik line chart pengumpulan vs penyaluran per bulan
     */
    public function grafik(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);

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

        return response()->json($data);
    }

    /**
     * GET /api/transaksi?limit=5&sort=terbaru
     * Daftar transaksi terbaru
     */
    public function transaksiTerbaru(Request $request)
    {
        $limit = (int) $request->query('limit', 5);
        $limit = min($limit, 50); // batasi max 50

        $transaksi = Transaksi::orderByDesc('created_at')
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
            });

        return response()->json($transaksi);
    }

    /**
     * GET /api/program?status=aktif
     * Daftar program penyaluran aktif
     */
    public function programAktif(Request $request)
    {
        $status = $request->query('status', 'aktif');
        $tahun  = $request->query('tahun', now()->year);

        $programs = ProgramPenyaluran::where('status', $status)
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
            });

        return response()->json($programs);
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
