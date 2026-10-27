<?php

namespace App\Http\Controllers;

use App\Models\ProgramPenyaluran;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    /**
     * GET /api/program
     * List dengan filter search, status, tahun, dan pagination.
     */
    public function index(Request $request)
    {
        $query = ProgramPenyaluran::with('transaksi')
            ->when($request->search, fn($q) =>
                $q->where(function ($inner) use ($request) {
                    $term = $request->search;
                    $inner->where('nama',    'ilike', "%{$term}%")
                          ->orWhere('kode',  'ilike', "%{$term}%")
                          ->orWhere('deskripsi', 'ilike', "%{$term}%");
                })
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->tahun,  fn($q) => $q->where('tahun',  (int) $request->tahun))
            ->orderByDesc('tahun')
            ->orderBy('kode');

        $perPage = min((int) $request->get('per_page', 10), 100);
        $data    = $query->paginate($perPage);

        return response()->json([
            'data' => $data->map(fn($p) => $this->fmt($p)),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    /**
     * GET /api/program/options
     * Hanya id + nama, untuk combobox di form penyaluran.
     */
    public function options(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);
        return response()->json(
            ProgramPenyaluran::where('status', 'aktif')
                ->where('tahun', $tahun)
                ->orderBy('nama')
                ->get(['id', 'nama', 'kode'])
        );
    }

    /**
     * POST /api/program
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'         => 'required|string|max:150',
            'deskripsi'    => 'nullable|string',
            'target_nominal' => 'required|integer|min:1',
            'status'       => 'nullable|in:aktif,selesai,ditunda',
            'tahun'        => 'nullable|integer|min:2000|max:2100',
        ]);

        $prog = ProgramPenyaluran::create([
            'kode'           => 'PRG-' . strtoupper(Str::random(6)),
            'nama'           => $validated['nama'],
            'deskripsi'      => $validated['deskripsi'] ?? null,
            'target_nominal' => $validated['target_nominal'],
            'status'         => $validated['status'] ?? 'aktif',
            'tahun'          => $validated['tahun'] ?? now()->year,
        ]);

        $prog->load('transaksi');
        return response()->json($this->fmt($prog), 201);
    }

    /**
     * PUT /api/program/{id}
     */
    public function update(Request $request, ProgramPenyaluran $program)
    {
        $validated = $request->validate([
            'nama'           => 'sometimes|required|string|max:150',
            'deskripsi'      => 'nullable|string',
            'target_nominal' => 'sometimes|required|integer|min:1',
            'status'         => 'nullable|in:aktif,selesai,ditunda',
            'tahun'          => 'nullable|integer|min:2000|max:2100',
        ]);

        $program->update($validated);
        $program->load('transaksi');
        return response()->json($this->fmt($program->fresh()->load('transaksi')));
    }

    /**
     * DELETE /api/program/{id}
     */
    public function destroy(ProgramPenyaluran $program)
    {
        $program->delete();
        return response()->json(['message' => 'Program berhasil dihapus.']);
    }

    /**
     * GET /api/public/program (PUBLIC — tanpa auth)
     */
    public function publicList(Request $request)
    {
        $tahun = $request->query('tahun', now()->year);

        $programs = ProgramPenyaluran::with('transaksi')
            ->where('status', 'aktif')
            ->when($tahun, fn($q) => $q->where('tahun', (int) $tahun))
            ->orderBy('id')
            ->get();

        // Jika tahun berjalan belum ada data, ambil dari tahun terbaru yang memiliki program aktif
        if ($programs->isEmpty()) {
            $latestYear = ProgramPenyaluran::where('status', 'aktif')->max('tahun');
            if ($latestYear) {
                $programs = ProgramPenyaluran::with('transaksi')
                    ->where('status', 'aktif')
                    ->where('tahun', $latestYear)
                    ->orderBy('id')
                    ->get();
            }
        }

        return response()->json([
            'data' => $programs->map(fn($p) => $this->fmt($p)),
        ]);
    }

    /**
     * GET /api/public/program/{idOrSlug} (PUBLIC — tanpa auth)
     * Mengembalikan detail program berdasarkan ID atau Slug, beserta transaksi donasi dan penyaluran terkini.
     */
    public function publicDetail($idOrSlug)
    {
        $relationLoader = [
            'transaksi' => fn($q) => $q->orderByDesc('created_at'),
            'transaksi.muzakki',
            'transaksi.mustahik',
        ];

        $program = null;

        // 1. Coba cari dengan numeric ID
        if (is_numeric($idOrSlug)) {
            $program = ProgramPenyaluran::with($relationLoader)->find($idOrSlug);
        }

        // 2. Jika bukan ID numeric atau tidak ditemukan, cari dengan slug / nama / kode
        if (!$program) {
            $allPrograms = ProgramPenyaluran::with($relationLoader)->get();
            $slugClean = Str::slug($idOrSlug);

            $program = $allPrograms->first(function ($p) use ($idOrSlug, $slugClean) {
                return $p->slug === $idOrSlug
                    || Str::slug($p->nama) === $slugClean
                    || Str::lower($p->kode) === Str::lower($idOrSlug);
            });
        }

        if (!$program) {
            return response()->json([
                'message' => 'Program tidak ditemukan.',
            ], 404);
        }

        $formatted = $this->fmt($program);

        // Tambahan info detail dinamis
        $formatted['persentase_terkumpul'] = $program->progress;
        $formatted['persentase_penyaluran'] = $program->target_nominal > 0
            ? (int) min(100, round(($program->nominal_disalurkan / $program->target_nominal) * 100))
            : 0;

        $formatted['sisa_target'] = max(0, (int) $program->target_nominal - (int) $program->nominal_terkumpul);

        // Riwayat donatur terbaru langsung dari database
        $formatted['riwayat_donasi'] = $program->transaksi
            ->where('jenis', 'masuk')
            ->values()
            ->take(10)
            ->map(function ($t) {
                $namaDonatur = $t->muzakki?->nama;
                if (!$namaDonatur && !empty($t->deskripsi)) {
                    if (preg_match('/oleh\s+(.+)$/i', $t->deskripsi, $matches)) {
                        $namaDonatur = trim($matches[1]);
                    } elseif (preg_match('/dari\s+([^u]+?)(?:\s+untuk|$)/i', $t->deskripsi, $matches)) {
                        $namaDonatur = trim($matches[1]);
                    }
                }

                return [
                    'id'           => $t->id,
                    'kode'         => $t->kode,
                    'nominal'      => (int) $t->nominal,
                    'tanggal'      => $t->created_at?->format('d M Y') ?? '',
                    'nama_donatur' => $namaDonatur ?: 'Hamba Allah',
                ];
            });

        // Riwayat penyaluran terbaru
        $formatted['riwayat_penyaluran'] = $program->transaksi
            ->where('jenis', 'keluar')
            ->values()
            ->take(8)
            ->map(fn($t) => [
                'id'         => $t->id,
                'kode'       => $t->kode,
                'nominal'    => (int) $t->nominal,
                'tanggal'    => $t->created_at?->format('d M Y') ?? '',
                'penerima'   => $t->mustahik?->nama
                    ? Str::mask($t->mustahik->nama, '*', 3, -2)
                    : 'Mustahik Terdaftar',
                'deskripsi'  => $t->deskripsi ?? 'Penyaluran bantuan program',
            ]);

        return response()->json([
            'data' => $formatted,
        ]);
    }

    // ── Helper ─────────────────────────────────────────────────────────────────
    private function fmt(ProgramPenyaluran $p): array
    {
        return [
            'id'                 => $p->id,
            'slug'               => $p->slug,
            'kode'               => $p->kode,
            'nama'               => $p->nama,
            'deskripsi'          => $p->deskripsi,
            'jumlah_penerima'    => $p->jumlah_penerima,     // computed
            'jumlah_donatur'     => $p->jumlah_donatur,      // computed dari transaksi masuk
            'target_nominal'     => (int) $p->target_nominal,
            'nominal_terkumpul'  => $p->nominal_terkumpul,   // computed dari transaksi masuk
            'nominal_disalurkan' => $p->nominal_disalurkan,  // computed dari transaksi keluar
            'progress'           => $p->progress,             // computed
            'status'             => $p->status,
            'tahun'              => (int) $p->tahun,
        ];
    }
}


