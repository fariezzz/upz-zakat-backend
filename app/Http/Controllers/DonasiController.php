<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DonasiController extends Controller
{
    /**
     * POST /api/donasi  (PUBLIC – tanpa auth)
     * Terima donasi online dari halaman publik.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori'     => 'required|string|max:50',
            'nominal'      => 'required|integer|min:10000',
            'metode'       => 'required|in:transfer-bank,qris,e-wallet',
            'anonim'       => 'boolean',
            'nama_donatur' => 'nullable|string|max:100',
            'email'        => 'nullable|email|max:100',
            'telepon'      => 'nullable|string|max:20',
            'keterangan'   => 'nullable|string|max:255',
        ]);

        $now  = now();
        $kode = 'DON-' . strtoupper(Str::random(6));

        $deskripsi = $validated['keterangan'] ?? null;

        if (!$deskripsi) {
            $deskripsi = 'Donasi Online – ' . $validated['kategori'];
            if (!empty($validated['nama_donatur']) && !($validated['anonim'] ?? false)) {
                $deskripsi .= ' oleh ' . $validated['nama_donatur'];
            }
        }

        $transaksi = Transaksi::create([
            'kode'      => $kode,
            'jenis'     => 'masuk',
            'kategori'  => $validated['kategori'],
            'deskripsi' => $deskripsi,
            'nominal'   => $validated['nominal'],
            'metode'    => $this->resolveMetodeLabel($validated['metode']),
            'tahun'     => $now->year,
            'bulan'     => $now->month,
        ]);

        return response()->json([
            'kode'       => $transaksi->kode,
            'kategori'   => $transaksi->kategori,
            'nominal'    => $transaksi->nominal,
            'metode'     => $transaksi->metode,
            'created_at' => $transaksi->created_at,
            'message'    => 'Donasi berhasil diterima. Terima kasih atas kepedulian Anda.',
        ], 201);
    }

    /**
     * GET /api/donasi  (PROTECTED)
     * Daftar donasi online untuk dashboard admin.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 10), 100);
        $search  = $request->query('search', '');

        $query = Transaksi::where('jenis', 'masuk')
            ->where('kode', 'like', 'DON-%')
            ->when($search, fn($q) => $q->where(function ($inner) use ($search) {
                $inner->where('kode', 'ilike', "%{$search}%")
                      ->orWhere('deskripsi', 'ilike', "%{$search}%")
                      ->orWhere('kategori', 'ilike', "%{$search}%");
            }))
            ->orderByDesc('created_at');

        $data = $query->paginate($perPage);

        return response()->json([
            'data' => $data->map(fn($t) => [
                'id'         => $t->id,
                'kode'       => $t->kode,
                'kategori'   => $t->kategori,
                'nominal'    => $t->nominal,
                'metode'     => $t->metode,
                'deskripsi'  => $t->deskripsi,
                'tanggal'    => $t->created_at->toDateTimeString(),
            ]),
            'meta' => [
                'current_page'  => $data->currentPage(),
                'last_page'     => $data->lastPage(),
                'per_page'      => $data->perPage(),
                'total'         => $data->total(),
                'total_nominal' => (clone $query)->sum('nominal'),
            ],
        ]);
    }

    private function resolveMetodeLabel(string $id): string
    {
        return match ($id) {
            'transfer-bank' => 'Transfer Bank',
            'qris'          => 'QRIS',
            'e-wallet'      => 'E-Wallet',
            default         => $id,
        };
    }
}
