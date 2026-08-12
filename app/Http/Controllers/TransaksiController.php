<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransaksiController extends Controller
{
    /**
     * GET /api/transaksi/pengumpulan
     * List transaksi jenis masuk dengan filter dan pagination.
     */
    public function indexPengumpulan(Request $request)
    {
        $query = Transaksi::with('muzakki')
            ->where('jenis', 'masuk')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('kode', 'ilike', "%{$request->search}%")
                          ->orWhereHas('muzakki', fn($m) => $m->where('nama', 'ilike', "%{$request->search}%"));
                });
            })
            ->when($request->kategori, fn($q) => $q->where('kategori', $request->kategori))
            ->when($request->bulan && $request->bulan !== '0', fn($q) => $q->where('bulan', (int) $request->bulan))
            ->orderByDesc('created_at');

        $totalNominal = (clone $query)->sum('nominal');
        $perPage = min((int) $request->get('per_page', 10), 100);
        $data    = $query->paginate($perPage);

        return response()->json([
            'data' => $data->map(fn($t) => [
                'id'       => $t->id,
                'kode'     => $t->kode,
                'nama'     => $t->muzakki?->nama ?? 'Anonim',
                'kategori' => $t->kategori,
                'nominal'  => $t->nominal,
                'metode'   => $t->metode,
                'tanggal'  => $t->created_at->toDateTimeString(),
                'keterangan' => $t->deskripsi,
            ]),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'total_nominal'=> $totalNominal,
            ],
        ]);
    }

    /**
     * GET /api/transaksi/penyaluran
     * List transaksi jenis keluar dengan filter dan pagination.
     */
    public function indexPenyaluran(Request $request)
    {
        $query = Transaksi::with('muzakki')
            ->where('jenis', 'keluar')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('kode', 'ilike', "%{$request->search}%")
                          ->orWhereHas('muzakki', fn($m) => $m->where('nama', 'ilike', "%{$request->search}%"));
                });
            })
            ->orderByDesc('created_at');

        $totalNominal = (clone $query)->sum('nominal');
        $perPage = min((int) $request->get('per_page', 10), 100);
        $data    = $query->paginate($perPage);

        return response()->json([
            'data' => $data->map(fn($t) => [
                'id'       => $t->id,
                'kode'     => $t->kode,
                'nama'     => $t->muzakki?->nama ?? 'Anonim',
                'nominal'  => $t->nominal,
                'metode'   => $t->metode,
                'tanggal'  => $t->created_at->toDateTimeString(),
                'keterangan' => $t->deskripsi,
            ]),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'total_nominal'=> $totalNominal,
            ],
        ]);
    }

    /**
     * POST /api/transaksi/pengumpulan
     */
    public function storePengumpulan(Request $request)
    {
        $validated = $request->validate([
            'muzakki_id' => 'nullable|exists:muzakki,id',
            'kategori'   => 'required|string|max:50',
            'nominal'    => 'required|integer|min:1',
            'metode'     => 'nullable|string|max:50',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $now  = now();
        $kode = 'TRX-M-' . strtoupper(Str::random(6));

        $transaksi = Transaksi::create([
            'kode'       => $kode,
            'jenis'      => 'masuk',
            'kategori'   => $validated['kategori'],
            'deskripsi'  => $validated['keterangan'] ?? ('Pengumpulan ' . $validated['kategori']),
            'nominal'    => $validated['nominal'],
            'metode'     => $validated['metode'] ?? null,
            'tahun'      => $now->year,
            'bulan'      => $now->month,
            'muzakki_id' => $validated['muzakki_id'] ?? null,
        ]);

        return response()->json($transaksi->load('muzakki'), 201);
    }

    /**
     * POST /api/transaksi/penyaluran
     */
    public function storePenyaluran(Request $request)
    {
        $validated = $request->validate([
            'muzakki_id' => 'nullable|exists:muzakki,id',
            'nominal'    => 'required|integer|min:1',
            'metode'     => 'nullable|string|max:50',
            'asnaf'      => 'nullable|string|max:50',
            'program'    => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $now  = now();
        $kode = 'TRX-K-' . strtoupper(Str::random(6));

        $desc = $validated['keterangan']
            ?? ('Penyaluran' . ($validated['program'] ? ' – ' . $validated['program'] : ''));

        $transaksi = Transaksi::create([
            'kode'       => $kode,
            'jenis'      => 'keluar',
            'kategori'   => 'Penyaluran',
            'deskripsi'  => $desc,
            'nominal'    => $validated['nominal'],
            'metode'     => $validated['metode'] ?? null,
            'tahun'      => $now->year,
            'bulan'      => $now->month,
            'muzakki_id' => $validated['muzakki_id'] ?? null,
        ]);

        return response()->json($transaksi->load('muzakki'), 201);
    }
}
