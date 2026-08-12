<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Muzakki;
use App\Models\ProgramPenyaluran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);
        return response()->json($this->buildStats($tahun));
    }

    public function ringkasanDana(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);
        return response()->json($this->buildRingkasanDana($tahun));
    }

    public function grafik(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);
        return response()->json($this->buildGrafik($tahun));
    }

    public function transaksiTerbaru(Request $request)
    {
        $limit = (int) $request->query('limit', 5);
        $limit = min($limit, 50);
        return response()->json($this->buildTransaksiTerbaru($limit));
    }

    public function programAktif(Request $request)
    {
        $status = $request->query('status', 'aktif');
        $tahun  = $request->query('tahun', now()->year);
        return response()->json($this->buildProgramAktif($status, $tahun));
    }

    public function all(Request $request)
    {
        // 'all' = semua waktu, angka = filter per tahun
        $tahun = $request->query('tahun', now()->year);

        return response()->json([
            'stats'         => $this->buildStats($tahun),
            'ringkasanDana' => $this->buildRingkasanDana($tahun),
            'grafik'        => $this->buildGrafik($tahun),
            'transaksi'     => $this->buildTransaksiTerbaru(5),
            'program'       => $this->buildProgramAktif('aktif', $tahun),
        ]);
    }

    // ——————————————————————————————————
    // Builder methods
    // ——————————————————————————————————

    /**
     * $tahun bisa berupa integer (tahun tertentu) atau string 'all' (semua waktu).
     */
    private function buildStats($tahun): array
    {
        $isAll = ($tahun === 'all');

        $qMasuk  = fn() => Transaksi::where('jenis', 'masuk');
        $qKeluar = fn() => Transaksi::where('jenis', 'keluar');

        $terkumpul   = $isAll ? $qMasuk()->sum('nominal')  : $qMasuk()->where('tahun', $tahun)->sum('nominal');
        $disalurkan  = $isAll ? $qKeluar()->sum('nominal') : $qKeluar()->where('tahun', $tahun)->sum('nominal');
        $saldoKasBank = $qMasuk()->sum('nominal') - $qKeluar()->sum('nominal');

        // Perbandingan YoY hanya relevan jika filter per-tahun
        $terkumpulLalu  = 0;
        $disalurkanLalu = 0;
        $muzakkiLalu    = 0;

        if (!$isAll) {
            $tahunLalu      = $tahun - 1;
            $terkumpulLalu  = $qMasuk()->where('tahun', $tahunLalu)->sum('nominal');
            $disalurkanLalu = $qKeluar()->where('tahun', $tahunLalu)->sum('nominal');
            $muzakkiLalu    = Muzakki::where('status', 'aktif')
                ->whereHas('transaksi', fn($q) => $q->where('tahun', $tahunLalu))->count();
        }

        $muzakki = $isAll
            ? Muzakki::where('status', 'aktif')->count()
            : Muzakki::where('status', 'aktif')
                ->whereHas('transaksi', fn($q) => $q->where('tahun', $tahun))->count();

        return [
            'totalDanaTerkumpul'      => (int) $terkumpul,
            'totalDanaDisalurkan'     => (int) $disalurkan,
            'saldoKasBank'            => (int) $saldoKasBank,
            'totalMuzakki'            => (int) $muzakki,
            'perubahanDanaTerkumpul'  => $isAll ? null : $this->hitungPerubahan($terkumpul, $terkumpulLalu),
            'perubahanDanaDisalurkan' => $isAll ? null : $this->hitungPerubahan($disalurkan, $disalurkanLalu),
            'perubahanMuzakki'        => $isAll ? null : $this->hitungPerubahan($muzakki, $muzakkiLalu),
        ];
    }

    private function buildRingkasanDana($tahun): array
    {
        $isAll = ($tahun === 'all');

        $kategoriColors = [
            'Zakat'        => '#2e7d38',
            'Infaq'        => '#3b82f6',
            'Sedekah'      => '#eab308',
            'Dana Lainnya' => '#a855f7',
        ];

        $query = Transaksi::where('jenis', 'masuk')
            ->select('kategori', DB::raw('SUM(nominal) as total'))
            ->groupBy('kategori');

        if (!$isAll) {
            $query->where('tahun', $tahun);
        }

        $rows = $query->get();

        $groupedData = [
            'Zakat'        => 0,
            'Infaq'        => 0,
            'Sedekah'      => 0,
            'Dana Lainnya' => 0,
        ];

        foreach ($rows as $row) {
            $cat = $row->kategori;
            if (stripos($cat, 'Zakat') !== false) {
                $groupedData['Zakat'] += $row->total;
            } elseif (isset($groupedData[$cat])) {
                $groupedData[$cat] += $row->total;
            } else {
                $groupedData['Dana Lainnya'] += $row->total;
            }
        }

        $grandTotal = array_sum($groupedData);
        $result = [];

        foreach ($groupedData as $name => $total) {
            $result[] = [
                'name'    => $name,
                'value'   => (int) $total,
                'percent' => $grandTotal > 0 ? round(($total / $grandTotal) * 100, 1) : 0,
                'color'   => $kategoriColors[$name],
            ];
        }

        return $result;
    }

    private function buildGrafik($tahun): array
    {
        $isAll     = ($tahun === 'all');
        $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        if ($isAll) {
            // Mode all-time: tampilkan per tahun (bukan per bulan)
            $masuk = Transaksi::where('jenis', 'masuk')
                ->select('tahun', DB::raw('SUM(nominal) as total'))
                ->groupBy('tahun')->orderBy('tahun')->pluck('total', 'tahun');

            $keluar = Transaksi::where('jenis', 'keluar')
                ->select('tahun', DB::raw('SUM(nominal) as total'))
                ->groupBy('tahun')->orderBy('tahun')->pluck('total', 'tahun');

            $allTahun = collect($masuk->keys())->merge($keluar->keys())->unique()->sort()->values();

            return $allTahun->map(fn($t) => [
                'bulan'       => (string) $t,
                'pengumpulan' => (int) ($masuk[$t] ?? 0),
                'penyaluran'  => (int) ($keluar[$t] ?? 0),
            ])->toArray();
        }

        // Mode per-tahun: tampilkan per bulan seperti biasa
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

    private function buildProgramAktif(string $status, $tahun): array
    {
        $isAll = ($tahun === 'all');

        $query = ProgramPenyaluran::with('transaksi')->where('status', $status);
        if (!$isAll) {
            $query->where('tahun', $tahun);
        }

        return $query->get()->map(fn($prog) => [
            'id'       => $prog->kode,
            'nama'     => $prog->nama,
            'penerima' => $prog->jumlah_penerima,   // computed dari transaksi
            'nominal'  => $prog->nominal_disalurkan, // computed dari transaksi
            'progress' => $prog->progress,           // computed dari transaksi
        ])->toArray();
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
