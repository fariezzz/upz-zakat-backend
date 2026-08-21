<?php

namespace App\Http\Controllers;

use App\Models\Muzakki;
use App\Models\Mustahik;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * GET /api/laporan/ringkasan?tahun=2025
     * Laporan keuangan komprehensif untuk 1 tahun.
     */
    public function ringkasan(Request $request)
    {
        $tahun = (int) $request->query('tahun', now()->year);

        /* ── Agregat Utama ─────────────────────────────── */
        $totalMasuk  = Transaksi::where('jenis', 'masuk')->where('tahun', $tahun)->sum('nominal');
        $totalKeluar = Transaksi::where('jenis', 'keluar')->where('tahun', $tahun)->sum('nominal');
        $saldoBersih = $totalMasuk - $totalKeluar;

        /* ── Jumlah SDM ─────────────────────────────────── */
        $totalMuzakki  = Muzakki::where('status', 'aktif')->count();
        $totalMustahik = Mustahik::where('status', 'aktif')->count();

        /* ── Total Donasi Online ────────────────────────── */
        $totalDonasi = Transaksi::where('jenis', 'masuk')
            ->where('tahun', $tahun)
            ->where('kode', 'like', 'DON-%')
            ->sum('nominal');

        $jumlahDonasi = Transaksi::where('jenis', 'masuk')
            ->where('tahun', $tahun)
            ->where('kode', 'like', 'DON-%')
            ->count();

        /* ── Per Kategori (dana masuk) ──────────────────── */
        $perKategori = Transaksi::where('jenis', 'masuk')
            ->where('tahun', $tahun)
            ->selectRaw('kategori, SUM(nominal) as total, COUNT(*) as jumlah')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'kategori' => $r->kategori,
                'total'    => (int) $r->total,
                'jumlah'   => (int) $r->jumlah,
            ]);

        /* ── Per Bulan ──────────────────────────────────── */
        $namabulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $masukPerBulan  = Transaksi::where('jenis', 'masuk')->where('tahun', $tahun)
            ->selectRaw('bulan, SUM(nominal) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $keluarPerBulan = Transaksi::where('jenis', 'keluar')->where('tahun', $tahun)
            ->selectRaw('bulan, SUM(nominal) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $perBulan = collect(range(1, 12))->map(fn($b) => [
            'bulan'  => $b,
            'label'  => $namabulan[$b],
            'masuk'  => (int) ($masukPerBulan[$b] ?? 0),
            'keluar' => (int) ($keluarPerBulan[$b] ?? 0),
        ]);

        /* ── Transaksi Terbaru (20) ─────────────────────── */
        $terbaru = Transaksi::where('tahun', $tahun)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($t) => [
                'kode'      => $t->kode,
                'jenis'     => $t->jenis,
                'kategori'  => $t->kategori,
                'nominal'   => $t->nominal,
                'tanggal'   => $t->created_at->toDateTimeString(),
            ]);

        return response()->json([
            'tahun'          => $tahun,
            'total_masuk'    => (int) $totalMasuk,
            'total_keluar'   => (int) $totalKeluar,
            'saldo_bersih'   => (int) $saldoBersih,
            'total_muzakki'  => $totalMuzakki,
            'total_mustahik' => $totalMustahik,
            'total_donasi'   => (int) $totalDonasi,
            'jumlah_donasi'  => $jumlahDonasi,
            'per_kategori'   => $perKategori,
            'per_bulan'      => $perBulan,
            'transaksi_terbaru' => $terbaru,
        ]);
    }

    /**
     * GET /api/public/laporan (PUBLIC — tanpa auth)
     */
    public function publicReport(Request $request)
    {
        $tahun = (int) $request->query('tahun', now()->year);

        $totalMasuk  = Transaksi::where('jenis', 'masuk')->where('tahun', $tahun)->sum('nominal');
        $totalKeluar = Transaksi::where('jenis', 'keluar')->where('tahun', $tahun)->sum('nominal');

        $totalMuzakki  = Muzakki::where('status', 'aktif')->count();
        $totalMustahik = Mustahik::where('status', 'aktif')->count();

        $penerimaan = Transaksi::where('jenis', 'masuk')
            ->where('tahun', $tahun)
            ->selectRaw('kategori as label, SUM(nominal) as amount')
            ->groupBy('kategori')
            ->orderByDesc('amount')
            ->get()
            ->map(fn($r) => ['label' => $r->label, 'amount' => (int) $r->amount]);

        $penyaluran = Transaksi::where('jenis', 'keluar')
            ->where('tahun', $tahun)
            ->selectRaw('kategori as label, SUM(nominal) as amount')
            ->groupBy('kategori')
            ->orderByDesc('amount')
            ->get()
            ->map(fn($r) => ['label' => $r->label, 'amount' => (int) $r->amount]);

        return response()->json([
            'tahun'          => $tahun,
            'total_masuk'    => (int) $totalMasuk,
            'total_keluar'   => (int) $totalKeluar,
            'total_muzakki'  => $totalMuzakki,
            'total_mustahik' => $totalMustahik,
            'penerimaan'     => $penerimaan,
            'penyaluran'     => $penyaluran,
        ]);
    }
}
