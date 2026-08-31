<?php

namespace App\Http\Controllers;

use App\Models\Muzakki;
use Illuminate\Http\Request;

class MuzakkiController extends Controller
{
    /**
     * GET /api/public/muzakki
     * Public transparency list (nama, kategori, jenis_zakat, count stats)
     */
    public function publicList(Request $request)
    {
        $search = $request->query('search');
        $kategori = $request->query('kategori');

        $query = Muzakki::query()
            ->with(['transaksi' => function ($q) {
                $q->where('jenis', 'masuk')->latest();
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'ilike', "%{$search}%")
                  ->orWhere('nik', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%")
                  ->orWhere('no_hp', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('unit_kerja', 'ilike', "%{$search}%")
                  ->orWhere('pekerjaan', 'ilike', "%{$search}%")
                  ->orWhere('alamat_lengkap', 'ilike', "%{$search}%")
                  ->orWhere('kategori', 'ilike', "%{$search}%");
            });
        }

        if ($kategori === 'unsil' || $kategori === 'dosen_staf') {
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

        $totalDosenStaf = Muzakki::where(function ($q) {
            $q->where('kategori', 'ilike', '%Dosen%')
              ->orWhere('kategori', 'ilike', '%Staf%')
              ->orWhere('kategori', 'ilike', '%Civitas%')
              ->orWhere(function ($q2) {
                  $q2->whereNotNull('unit_kerja')
                     ->where('unit_kerja', '!=', '')
                     ->where('unit_kerja', '!=', 'Masyarakat Umum')
                     ->where('unit_kerja', '!=', 'Umum');
              });
        })->count();

        $totalUmum = Muzakki::where(function ($q) {
            $q->where('kategori', 'ilike', '%Umum%')
              ->orWhere(function ($q2) {
                  $q2->whereNull('unit_kerja')
                     ->orWhere('unit_kerja', '')
                     ->orWhere('unit_kerja', 'Masyarakat Umum')
                     ->orWhere('unit_kerja', 'Umum');
              });
        })->count();

        $list = $allMuzakki->map(function ($m) {
            $isUnsil = (!empty($m->kategori) && (stripos($m->kategori, 'Dosen') !== false || stripos($m->kategori, 'Staf') !== false || stripos($m->kategori, 'UNSIL') !== false))
                || (!empty($m->unit_kerja) && !in_array($m->unit_kerja, ['Masyarakat Umum', 'Umum']));
            
            $kategoriLabel = $m->kategori ?: ($isUnsil ? 'Dosen & Staf UNSIL' : 'Muzakki Umum');

            return [
                'id'                => $m->id,
                'nama'              => $m->nama,
                'nik'               => $m->nik,
                'nip'               => $m->nip,
                'jenis_kelamin'     => $m->jenis_kelamin,
                'tempat_lahir'      => $m->tempat_lahir,
                'tanggal_lahir'     => $m->tanggal_lahir,
                'pekerjaan'         => $m->pekerjaan,
                'alamat_lengkap'    => $m->alamat_lengkap,
                'email'             => $m->email,
                'no_hp'             => $m->no_hp,
                'unit_kerja'        => $m->unit_kerja,
                'kategori'          => $kategoriLabel,
                'jenis_zakat'       => $m->jenis_zakat,
                'frekuensi'         => $m->frekuensi,
                'nominal'           => $m->nominal,
                'kesepakatan_zakat' => $m->kesepakatan_zakat,
                'metode_pembayaran' => $m->metode_pembayaran,
                'pilihan_bank'      => $m->pilihan_bank,
                'pilihan_ewallet'   => $m->pilihan_ewallet,
                'created_at'        => $m->created_at ? $m->created_at->toISOString() : null,
                'tanggal_daftar'    => $m->created_at ? $m->created_at->translatedFormat('d M Y') : '-',
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
            'jenis_kelamin'     => 'nullable|string|max:20',
            'tempat_lahir'      => 'nullable|string|max:100',
            'tanggal_lahir'     => 'nullable|string|max:50',
            'pekerjaan'         => 'nullable|string|max:100',
            'alamat_lengkap'    => 'nullable|string',
            'email'             => 'nullable|string|max:100',
            'no_hp'             => 'nullable|string|max:25',
            'kategori'          => 'nullable|string|max:50',
            'unit_kerja'        => 'nullable|string|max:200',
            'jenis_zakat'       => 'nullable|string|max:200',
            'frekuensi'         => 'nullable|string|max:100',
            'nominal'           => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|max:100',
            'pilihan_bank'      => 'nullable|string|max:100',
            'pilihan_ewallet'   => 'nullable|string|max:100',
            'kesepakatan_zakat' => 'nullable',
        ]);

        $kategoriDefault = !empty($validated['kategori'])
            ? $validated['kategori']
            : (!empty($validated['nip']) ? 'Dosen & Staf UNSIL' : 'Muzakki Umum');

        $kesepakatan = $validated['kesepakatan_zakat'] ?? null;
        if (is_string($kesepakatan)) {
            $decoded = json_decode($kesepakatan, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $kesepakatan = $decoded;
            }
        }

        // Hitung total nominal & join jenis zakat jika kesepakatan berbentuk array
        $nominalTotal = isset($validated['nominal']) ? (float) $validated['nominal'] : null;
        $jenisZakatSummary = $validated['jenis_zakat'] ?? null;

        if (is_array($kesepakatan) && count($kesepakatan) > 0) {
            $sum = 0;
            $labels = [];
            foreach ($kesepakatan as $item) {
                $sum += (float) ($item['nominal'] ?? 0);
                if (!empty($item['jenis'])) {
                    $labels[] = $item['jenis'];
                }
            }
            if ($nominalTotal === null || $nominalTotal <= 0) {
                $nominalTotal = $sum;
            }
            if (empty($jenisZakatSummary) && count($labels) > 0) {
                $jenisZakatSummary = implode(', ', array_unique($labels));
            }
        }

        $muzakki = Muzakki::updateOrCreate(
            ['nama' => $validated['nama']],
            [
                'nik'               => $validated['nik'] ?? null,
                'nip'               => $validated['nip'] ?? null,
                'jenis_kelamin'     => $validated['jenis_kelamin'] ?? null,
                'tempat_lahir'      => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir'     => $validated['tanggal_lahir'] ?? null,
                'pekerjaan'         => $validated['pekerjaan'] ?? null,
                'alamat_lengkap'    => $validated['alamat_lengkap'] ?? null,
                'email'             => $validated['email'] ?? null,
                'no_hp'             => $validated['no_hp'] ?? null,
                'kategori'          => $kategoriDefault,
                'unit_kerja'        => $validated['unit_kerja'] ?? ($kategoriDefault === 'Muzakki Umum' ? 'Masyarakat Umum' : null),
                'jenis_zakat'       => $jenisZakatSummary,
                'frekuensi'         => $validated['frekuensi'] ?? null,
                'nominal'           => $nominalTotal,
                'metode_pembayaran' => $validated['metode_pembayaran'] ?? null,
                'pilihan_bank'      => $validated['pilihan_bank'] ?? null,
                'pilihan_ewallet'   => $validated['pilihan_ewallet'] ?? null,
                'kesepakatan_zakat' => $kesepakatan,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran Muzakki berhasil! Anda kini terdaftar sebagai Muzakki UPZ Zakat UNSIL.',
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
                  ->orWhere('no_hp', 'ilike', "%{$search}%")
                  ->orWhere('unit_kerja', 'ilike', "%{$search}%")
                  ->orWhere('pekerjaan', 'ilike', "%{$search}%")
                  ->orWhere('alamat_lengkap', 'ilike', "%{$search}%")
                  ->orWhere('tempat_lahir', 'ilike', "%{$search}%")
                  ->orWhere('kategori', 'ilike', "%{$search}%");
            });
        }

        if ($kategori = $request->query('kategori')) {
            if ($kategori === 'dosen_staf' || $kategori === 'dosen/staf') {
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
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $data = $query->withCount('transaksi')->orderByDesc('created_at')->paginate($perPage);

        $totalDosenStaf = Muzakki::where(function ($q) {
            $q->where('kategori', 'ilike', '%Dosen%')
              ->orWhere('kategori', 'ilike', '%Staf%')
              ->orWhere('kategori', 'ilike', '%Civitas%')
              ->orWhere(function ($q2) {
                  $q2->whereNotNull('unit_kerja')
                     ->where('unit_kerja', '!=', '')
                     ->where('unit_kerja', '!=', 'Masyarakat Umum')
                     ->where('unit_kerja', '!=', 'Umum');
              });
        })->count();

        $totalUmum = Muzakki::where(function ($q) {
            $q->where('kategori', 'ilike', '%Umum%')
              ->orWhere(function ($q2) {
                  $q2->whereNull('unit_kerja')
                     ->orWhere('unit_kerja', '')
                     ->orWhere('unit_kerja', 'Masyarakat Umum')
                     ->orWhere('unit_kerja', 'Umum');
              });
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
            ->get(['id', 'nama', 'unit_kerja', 'kategori']);

        return response()->json($data);
    }

    /**
     * POST /api/muzakki
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'              => 'required|string|max:150',
            'nik'               => 'nullable|string|max:30',
            'nip'               => 'nullable|string|max:30',
            'jenis_kelamin'     => 'nullable|string|max:20',
            'tempat_lahir'      => 'nullable|string|max:100',
            'tanggal_lahir'     => 'nullable|string|max:50',
            'pekerjaan'         => 'nullable|string|max:100',
            'alamat_lengkap'    => 'nullable|string',
            'email'             => 'nullable|email|max:100',
            'no_hp'             => 'nullable|string|max:25',
            'kategori'          => 'nullable|string|max:50',
            'unit_kerja'        => 'nullable|string|max:200',
            'jenis_zakat'       => 'nullable|string|max:100',
            'frekuensi'         => 'nullable|string|max:50',
            'nominal'           => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|max:100',
        ]);

        $muzakki = Muzakki::create($validated);

        return response()->json($muzakki, 201);
    }

    /**
     * PUT /api/muzakki/{id}
     */
    public function update(Request $request, Muzakki $muzakki)
    {
        $validated = $request->validate([
            'nama'              => 'sometimes|required|string|max:150',
            'nik'               => 'nullable|string|max:30',
            'nip'               => 'nullable|string|max:30',
            'jenis_kelamin'     => 'nullable|string|max:20',
            'tempat_lahir'      => 'nullable|string|max:100',
            'tanggal_lahir'     => 'nullable|string|max:50',
            'pekerjaan'         => 'nullable|string|max:100',
            'alamat_lengkap'    => 'nullable|string',
            'email'             => 'nullable|email|max:100',
            'no_hp'             => 'nullable|string|max:25',
            'kategori'          => 'nullable|string|max:50',
            'unit_kerja'        => 'nullable|string|max:200',
            'jenis_zakat'       => 'nullable|string|max:100',
            'frekuensi'         => 'nullable|string|max:50',
            'nominal'           => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|max:100',
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

