<?php

namespace App\Http\Controllers;

use App\Models\Muzakki;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagihanController extends Controller
{
    /**
     * GET /api/tagihan
     * Mengambil daftar tagihan kesepakatan zakat per periode (bulan & tahun)
     * beserta status pelunasan dan ringkasan statistik.
     */
    public function index(Request $request)
    {
        $tahun    = (int) ($request->query('tahun') ?: now()->year);
        $bulan    = (int) ($request->query('bulan') ?: now()->month); // 1-12, atau 0 untuk tahunan
        $status   = $request->query('status', 'all'); // 'all' | 'lunas' | 'belum_bayar' | 'sebagian'
        $kategori = $request->query('kategori', 'all'); // 'all' | 'dosen_staf' | 'umum'
        $search   = trim((string) $request->query('search', ''));
        $perPage  = min((int) ($request->query('per_page', 10)), 100);

        // Ambil semua muzakki aktif
        $query = Muzakki::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%")
                  ->orWhere('nik', 'ilike', "%{$search}%")
                  ->orWhere('no_hp', 'ilike', "%{$search}%")
                  ->orWhere('unit_kerja', 'ilike', "%{$search}%")
                  ->orWhere('pekerjaan', 'ilike', "%{$search}%");
            });
        }

        if ($kategori === 'dosen_staf') {
            $query->where(function ($q) {
                $q->where('kategori', 'ilike', '%Dosen%')
                  ->orWhere('kategori', 'ilike', '%Staf%')
                  ->orWhere('kategori', 'ilike', '%Civitas%')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('unit_kerja')
                         ->where('unit_kerja', '!=', '')
                         ->where('unit_kerja', '!=', 'Masyarakat Umum')
                         ->where('unit_kerja', '!=', 'Umum');
                  });
            });
        } elseif ($kategori === 'umum') {
            $query->where(function ($q) {
                $q->where('kategori', 'ilike', '%Umum%')
                  ->orWhere(function ($q2) {
                      $q2->whereNull('unit_kerja')
                         ->orWhere('unit_kerja', '')
                         ->orWhere('unit_kerja', 'Masyarakat Umum')
                         ->orWhere('unit_kerja', 'Umum');
                  });
            });
        }

        $allMuzakki = $query->orderBy('nama')->get();

        // Ambil seluruh transaksi masuk pada tahun & bulan tersebut
        $trxQuery = Transaksi::where('jenis', 'masuk')
            ->where('tahun', $tahun);

        if ($bulan > 0) {
            $trxQuery->where('bulan', $bulan);
        }

        $transaksiList = $trxQuery->get()->groupBy('muzakki_id');

        // Olah data tagihan untuk tiap muzakki
        $items = [];
        $statTotalTarget    = 0;
        $statTotalDibayar   = 0;
        $statTotalBelum     = 0;
        $statJumlahLunas    = 0;
        $statJumlahSebagian = 0;
        $statJumlahBelum    = 0;

        foreach ($allMuzakki as $m) {
            $kesepakatan = is_array($m->kesepakatan_zakat) ? $m->kesepakatan_zakat : [];
            
            // 1. Ekstrak SEMUA komitmen kesepakatan muzakki untuk ditampilkan di tabel
            $rincianKesepakatan = [];

            if (!empty($kesepakatan)) {
                foreach ($kesepakatan as $k) {
                    $frekuensi = strtolower($k['frekuensi'] ?? 'bulanan');
                    $nominalItem = (float) ($k['nominal'] ?? 0);
                    
                    $rincianKesepakatan[] = [
                        'jenis'     => $k['jenis'] ?? 'Zakat',
                        'frekuensi' => $frekuensi,
                        'nominal'   => $nominalItem,
                        'detail'    => $k['detail'] ?? $frekuensi,
                    ];
                }
            }

            // Jika kesepakatan_zakat kosong tapi ada field nominal / jenis_zakat di profil muzakki
            if (empty($rincianKesepakatan)) {
                $nominalFallback = (float) ($m->nominal ?: 0);
                if ($nominalFallback > 0) {
                    $rincianKesepakatan[] = [
                        'jenis'     => $m->jenis_zakat ?: 'Zakat Penghasilan',
                        'frekuensi' => $m->frekuensi ?: 'bulanan',
                        'nominal'   => $nominalFallback,
                        'detail'    => 'Komitmen Zakat Rutin',
                    ];
                }
            }

            // Target nominal adalah SUM dari seluruh komitmen kesepakatan zakat muzakki
            $targetNominal = (float) collect($rincianKesepakatan)->sum('nominal');

            // Jika masih 0 namun ada nominal di profil, jadikan target
            if ($targetNominal <= 0 && (float) $m->nominal > 0) {
                $targetNominal = (float) $m->nominal;
            }

            // Transaksi yang sudah dibayarkan muzakki ini pada periode tersebut
            $trxMuzakki = $transaksiList->get($m->id, collect());
            $totalDibayar = (float) $trxMuzakki->sum('nominal');
            $sisaTagihan = max(0, $targetNominal - $totalDibayar);

            // Tentukan status
            if ($targetNominal > 0) {
                if ($totalDibayar >= $targetNominal) {
                    $statusBayar = 'lunas';
                    $statJumlahLunas++;
                } elseif ($totalDibayar > 0) {
                    $statusBayar = 'sebagian';
                    $statJumlahSebagian++;
                } else {
                    $statusBayar = 'belum_bayar';
                    $statJumlahBelum++;
                }
            } else {
                if ($totalDibayar > 0) {
                    $statusBayar = 'lunas';
                    $statJumlahLunas++;
                } else {
                    $statusBayar = 'bebas_tagihan';
                }
            }

            $statTotalTarget  += $targetNominal;
            $statTotalDibayar += $totalDibayar;
            $statTotalBelum   += $sisaTagihan;

            // Filter status jika dipilih
            if ($status !== 'all') {
                if ($status === 'lunas' && $statusBayar !== 'lunas') continue;
                if ($status === 'belum_bayar' && $statusBayar !== 'belum_bayar') continue;
                if ($status === 'sebagian' && $statusBayar !== 'sebagian') continue;
            }

            $isUnsil = (!empty($m->kategori) && (stripos($m->kategori, 'Dosen') !== false || stripos($m->kategori, 'Staf') !== false || stripos($m->kategori, 'UNSIL') !== false))
                || (!empty($m->unit_kerja) && !in_array($m->unit_kerja, ['Masyarakat Umum', 'Umum']));

            $items[] = [
                'id'                => $m->id,
                'muzakki_id'        => $m->id,
                'nama'              => $m->nama,
                'nip'               => $m->nip,
                'nik'               => $m->nik,
                'no_hp'             => $m->no_hp,
                'email'             => $m->email,
                'kategori'          => $m->kategori ?: ($isUnsil ? 'Dosen & Staf UNSIL' : 'Muzakki Umum'),
                'unit_kerja'        => $m->unit_kerja ?: ($isUnsil ? 'Civitas Akademika' : 'Masyarakat Umum'),
                'target_nominal'    => $targetNominal,
                'total_dibayar'     => $totalDibayar,
                'sisa_tagihan'      => $sisaTagihan,
                'status_bayar'      => $statusBayar,
                'rincian_kesepakatan'=> $rincianKesepakatan,
                'transaksi_terkait' => $trxMuzakki->map(fn($t) => [
                    'id'         => $t->id,
                    'kode'       => $t->kode,
                    'nominal'    => $t->nominal,
                    'kategori'   => $t->kategori,
                    'metode'     => $t->metode,
                    'tanggal'    => $t->created_at ? $t->created_at->translatedFormat('d M Y, H:i') : '-',
                    'keterangan' => $t->deskripsi,
                ])->values(),
            ];
        }

        // Pagination array
        $page = (int) ($request->query('page', 1));
        $offset = ($page - 1) * $perPage;
        $paginatedItems = array_slice($items, $offset, $perPage);
        $totalItems = count($items);
        $lastPage = (int) ceil($totalItems / max($perPage, 1));

        return response()->json([
            'data' => $paginatedItems,
            'meta' => [
                'current_page'  => $page,
                'last_page'     => max(1, $lastPage),
                'per_page'      => $perPage,
                'total'         => $totalItems,
                'total_nominal' => $statTotalTarget,
                'total_dibayar' => $statTotalDibayar,
                'total_belum'   => $statTotalBelum,
                'jumlah_lunas'  => $statJumlahLunas,
                'jumlah_belum'  => $statJumlahBelum,
            ],
        ]);
    }

    /**
     * POST /api/tagihan/catat-bayar
     * Mencatat pembayaran / pelunasan tagihan muzakki secara langsung oleh Admin
     */
    public function catatBayar(Request $request)
    {
        $validated = $request->validate([
            'muzakki_id' => 'required|exists:muzakki,id',
            'nominal'    => 'required|numeric|min:1000',
            'kategori'   => 'nullable|string|max:100',
            'metode'     => 'required|string|max:50',
            'tahun'      => 'required|integer|min:2020|max:2030',
            'bulan'      => 'required|integer|min:1|max:12',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $muzakki = Muzakki::findOrFail($validated['muzakki_id']);
        $kode = 'TRX-M-' . strtoupper(Str::random(6));

        $kategori = $validated['kategori'] ?: ($muzakki->jenis_zakat ?: 'Zakat Penghasilan');
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][$validated['bulan']] ?? 'Bulan ' . $validated['bulan'];

        $deskripsi = $validated['keterangan'] ?: ("Pembayaran Tagihan Zakat {$namaBulan} {$validated['tahun']} — {$muzakki->nama}");

        $transaksi = Transaksi::create([
            'kode'       => $kode,
            'jenis'      => 'masuk',
            'kategori'   => $kategori,
            'deskripsi'  => $deskripsi,
            'nominal'    => (int) $validated['nominal'],
            'metode'     => $validated['metode'],
            'tahun'      => $validated['tahun'],
            'bulan'      => $validated['bulan'],
            'muzakki_id' => $muzakki->id,
        ]);

        return response()->json([
            'message'   => "Pembayaran zakat atas nama {$muzakki->nama} sebesar Rp " . number_format($transaksi->nominal, 0, ',', '.') . " berhasil dicatat.",
            'transaksi' => $transaksi->load('muzakki'),
        ], 201);
    }
}
