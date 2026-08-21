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
        $programs = ProgramPenyaluran::with('transaksi')
            ->where('status', 'aktif')
            ->orderByDesc('tahun')
            ->orderBy('kode')
            ->get();

        return response()->json([
            'data' => $programs->map(fn($p) => $this->fmt($p)),
        ]);
    }

    // ── Helper ─────────────────────────────────────────────────────────────────
    private function fmt(ProgramPenyaluran $p): array
    {
        return [
            'id'                 => $p->id,
            'kode'               => $p->kode,
            'nama'               => $p->nama,
            'deskripsi'          => $p->deskripsi,
            'jumlah_penerima'    => $p->jumlah_penerima,     // computed
            'target_nominal'     => (int) $p->target_nominal,
            'nominal_disalurkan' => $p->nominal_disalurkan,  // computed
            'progress'           => $p->progress,             // computed
            'status'             => $p->status,
            'tahun'              => (int) $p->tahun,
        ];
    }
}
