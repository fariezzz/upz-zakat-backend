<?php

namespace App\Http\Controllers;

use App\Models\Muzakki;
use Illuminate\Http\Request;

class MuzakkiController extends Controller
{
    /**
     * GET /api/public/muzakki
     * Public transparency list (nama, kategori, jenis_zakat, status, count stats)
     */
    public function publicList(Request $request)
    {
        $search = $request->query('search');
        $kategori = $request->query('kategori');

        $query = Muzakki::where('status', 'aktif')
            ->with(['transaksi' => function ($q) {
                $q->where('jenis', 'masuk')->latest();
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'ilike', "%{$search}%")
                  ->orWhere('unit_kerja', 'ilike', "%{$search}%");
            });
        }

        if ($kategori === 'unsil' || $kategori === 'dosen_staf') {
            $query->whereNotNull('unit_kerja')
                  ->where('unit_kerja', '!=', '')
                  ->where('unit_kerja', '!=', 'Masyarakat Umum')
                  ->where('unit_kerja', '!=', 'Umum');
        } elseif ($kategori === 'umum') {
            $query->where(function ($q) {
                $q->whereNull('unit_kerja')
                  ->orWhere('unit_kerja', '')
                  ->orWhere('unit_kerja', 'Masyarakat Umum')
                  ->orWhere('unit_kerja', 'Umum');
            });
        }

        $allMuzakki = $query->orderBy('nama')->get();

        $totalDosenStaf = Muzakki::where('status', 'aktif')
            ->whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->where('unit_kerja', '!=', 'Masyarakat Umum')
            ->where('unit_kerja', '!=', 'Umum')
            ->count();

        $totalUmum = Muzakki::where('status', 'aktif')
            ->where(function ($q) {
                $q->whereNull('unit_kerja')
                  ->orWhere('unit_kerja', '')
                  ->orWhere('unit_kerja', 'Masyarakat Umum')
                  ->orWhere('unit_kerja', 'Umum');
            })->count();

        $list = $allMuzakki->map(function ($m) {
            $isUnsil = !empty($m->unit_kerja) && !in_array($m->unit_kerja, ['Masyarakat Umum', 'Umum']);
            $kategoriLabel = $isUnsil ? 'Dosen & Staf UNSIL' : 'Muzakki Umum';

            // Ambil jenis zakat dari transaksi terakhir jika ada, atau default sesuai profil
            $lastTrx = $m->transaksi->first();
            $jenisZakat = $lastTrx ? $lastTrx->kategori : ($isUnsil ? 'Zakat Penghasilan' : 'Zakat Maal');

            return [
                'id'         => $m->id,
                'nama'       => $m->nama,
                'unit_kerja' => $m->unit_kerja,
                'kategori'   => $kategoriLabel,
                'jenisZakat' => $jenisZakat,
                'status'     => ucfirst($m->status ?? 'Aktif'),
            ];
        });

        return response()->json([
            'data'  => $list,
            'stats' => [
                'total'      => $totalDosenStaf + $totalUmum,
                'dosen_staf' => $totalDosenStaf,
                'umum'       => $totalUmum,
            ],
        ]);
    }

    /**
     * POST /api/public/muzakki/register
     * Pendaftaran muzakki baru dari halaman publik
     */
    public function publicRegister(Request $request)
    {
        $validated = $request->validate([
            'nama'              => 'required|string|max:150',
            'nik'               => 'nullable|string|max:30',
            'nip'               => 'nullable|string|max:30',
            'email'             => 'nullable|string|max:100',
            'no_hp'             => 'nullable|string|max:25',
            'unit_kerja'        => 'nullable|string|max:200',
            'jenis_zakat'       => 'nullable|string|max:100',
            'frekuensi'         => 'nullable|string|max:50',
            'nominal'           => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|max:100',
        ]);

        $muzakki = Muzakki::updateOrCreate(
            ['nama' => $validated['nama']],
            [
                'nik'        => $validated['nik'] ?? null,
                'nip'        => $validated['nip'] ?? null,
                'email'      => $validated['email'] ?? null,
                'no_hp'      => $validated['no_hp'] ?? null,
                'unit_kerja' => $validated['unit_kerja'] ?? 'Masyarakat Umum',
                'status'     => 'aktif',
            ]
        );

        // Jika ada nominal dan pembayaran, simpan transaksi
        if (!empty($validated['nominal']) && $validated['nominal'] >= 10000) {
            $kategori = !empty($validated['jenis_zakat']) ? ucfirst($validated['jenis_zakat']) : 'Zakat Penghasilan';
            if ($kategori === 'Penghasilan') $kategori = 'Zakat Profesi';
            if ($kategori === 'Maal') $kategori = 'Zakat Maal';
            if ($kategori === 'Fitrah') $kategori = 'Zakat Fitrah';

            \App\Models\Transaksi::create([
                'kode'        => 'TRX-MASUK-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'jenis'       => 'masuk',
                'kategori'    => $kategori,
                'deskripsi'   => 'Penerimaan ' . $kategori . ' dari ' . $muzakki->nama . ' (Pendaftaran Muzakki Online)',
                'nominal'     => $validated['nominal'],
                'metode'      => !empty($validated['metode_pembayaran']) ? ucfirst($validated['metode_pembayaran']) : 'Transfer Bank',
                'tahun'       => (int) now()->year,
                'bulan'       => (int) now()->month,
                'muzakki_id'  => $muzakki->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran Muzakki berhasil!',
            'data'    => $muzakki,
        ], 201);
    }

    /**
     * GET /api/muzakki
     * Query params: search, kategori, per_page, page
     */
    public function index(Request $request)
    {
        $query = Muzakki::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'ilike', "%{$search}%")
                  ->orWhere('nik', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('unit_kerja', 'ilike', "%{$search}%");
            });
        }

        if ($kategori = $request->query('kategori')) {
            if ($kategori === 'dosen_staf' || $kategori === 'dosen/staf') {
                $query->whereNotNull('unit_kerja')
                      ->where('unit_kerja', '!=', '')
                      ->where('unit_kerja', '!=', 'Masyarakat Umum')
                      ->where('unit_kerja', '!=', 'Umum');
            } elseif ($kategori === 'umum') {
                $query->where(function ($q) {
                    $q->whereNull('unit_kerja')
                      ->orWhere('unit_kerja', '')
                      ->orWhere('unit_kerja', 'Masyarakat Umum')
                      ->orWhere('unit_kerja', 'Umum');
                });
            }
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $data = $query->withCount('transaksi')->orderByDesc('created_at')->paginate($perPage);

        $totalDosenStaf = Muzakki::whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->where('unit_kerja', '!=', 'Masyarakat Umum')
            ->where('unit_kerja', '!=', 'Umum')
            ->count();

        $totalUmum = Muzakki::where(function ($q) {
            $q->whereNull('unit_kerja')
              ->orWhere('unit_kerja', '')
              ->orWhere('unit_kerja', 'Masyarakat Umum')
              ->orWhere('unit_kerja', 'Umum');
        })->count();

        return response()->json([
            'data'  => $data->items(),
            'meta'  => [
                'current_page'      => $data->currentPage(),
                'last_page'         => $data->lastPage(),
                'per_page'          => $data->perPage(),
                'total'             => $data->total(),
                'total_dosen_staf'  => $totalDosenStaf,
                'total_umum'        => $totalUmum,
            ],
        ]);
    }


    /**
     * GET /api/muzakki/options
     * Untuk combobox — mengembalikan id + nama saja (ringan)
     */
    public function options(Request $request)
    {
        $search = $request->query('search', '');

        $data = Muzakki::query()
            ->when($search, fn($q) => $q->where('nama', 'ilike', "%{$search}%"))
            ->orderBy('nama')
            ->limit(30)
            ->get(['id', 'nama', 'unit_kerja']);

        return response()->json($data);
    }

    /**
     * POST /api/muzakki
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:100',
            'nik'        => 'nullable|string|max:30',
            'nip'        => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:100',
            'no_hp'      => 'nullable|string|max:20',
            'unit_kerja' => 'nullable|string|max:100',
        ]);

        $validated['status'] = 'aktif';

        $muzakki = Muzakki::create($validated);

        return response()->json($muzakki, 201);
    }

    /**
     * PUT /api/muzakki/{id}
     */
    public function update(Request $request, Muzakki $muzakki)
    {
        $validated = $request->validate([
            'nama'       => 'sometimes|required|string|max:100',
            'nik'        => 'nullable|string|max:30',
            'nip'        => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:100',
            'no_hp'      => 'nullable|string|max:20',
            'unit_kerja' => 'nullable|string|max:100',
        ]);

        $muzakki->update($validated);

        return response()->json($muzakki);
    }

    /**
     * DELETE /api/muzakki/{id}
     */
    public function destroy(Muzakki $muzakki)
    {
        $muzakki->delete();

        return response()->json(['message' => 'Muzakki berhasil dihapus.']);
    }
}
