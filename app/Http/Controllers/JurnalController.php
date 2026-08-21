<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use Illuminate\Http\Request;

class JurnalController extends Controller
{
    /**
     * GET /api/jurnal
     * Daftar jurnal dengan pagination, search, filter jenis & tanggal.
     */
    public function index(Request $request)
    {
        $query = Jurnal::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('nama_akun',   'ilike', "%{$request->search}%")
                          ->orWhere('kode_akun',  'ilike', "%{$request->search}%")
                          ->orWhere('keterangan', 'ilike', "%{$request->search}%")
                          ->orWhere('referensi',  'ilike', "%{$request->search}%");
                });
            })
            ->when($request->jenis, fn($q) => $q->where('jenis', $request->jenis))
            ->when($request->date_from, fn($q) => $q->where('tanggal', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->where('tanggal', '<=', $request->date_to))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at');

        // Summary aggregate (before pagination)
        $totalDebit  = (clone $query)->sum('debit');
        $totalKredit = (clone $query)->sum('kredit');

        $perPage = min((int) $request->get('per_page', 15), 100);
        $data    = $query->paginate($perPage);

        return response()->json([
            'data' => $data->map(fn($j) => [
                'id'         => $j->id,
                'tanggal'    => $j->tanggal->format('Y-m-d'),
                'kode_akun'  => $j->kode_akun,
                'nama_akun'  => $j->nama_akun,
                'keterangan' => $j->keterangan,
                'debit'      => $j->debit,
                'kredit'     => $j->kredit,
                'jenis'      => $j->jenis,
                'referensi'  => $j->referensi,
            ]),
            'meta' => [
                'current_page'  => $data->currentPage(),
                'last_page'     => $data->lastPage(),
                'per_page'      => $data->perPage(),
                'total'         => $data->total(),
                'total_debit'   => $totalDebit,
                'total_kredit'  => $totalKredit,
                'saldo_bersih'  => $totalDebit - $totalKredit,
            ],
        ]);
    }

    /**
     * POST /api/jurnal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'nama_akun'  => 'required|string|max:100',
            'kode_akun'  => 'nullable|string|max:20',
            'keterangan' => 'nullable|string',
            'debit'      => 'required|integer|min:0',
            'kredit'     => 'required|integer|min:0',
            'jenis'      => 'required|in:masuk,keluar',
            'referensi'  => 'nullable|string|max:50',
        ]);

        $jurnal = Jurnal::create($validated);

        return response()->json([
            'message' => 'Entri jurnal berhasil ditambahkan.',
            'data'    => $jurnal,
        ], 201);
    }

    /**
     * PUT /api/jurnal/{jurnal}
     */
    public function update(Request $request, Jurnal $jurnal)
    {
        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'nama_akun'  => 'required|string|max:100',
            'kode_akun'  => 'nullable|string|max:20',
            'keterangan' => 'nullable|string',
            'debit'      => 'required|integer|min:0',
            'kredit'     => 'required|integer|min:0',
            'jenis'      => 'required|in:masuk,keluar',
            'referensi'  => 'nullable|string|max:50',
        ]);

        $jurnal->update($validated);

        return response()->json([
            'message' => 'Entri jurnal berhasil diperbarui.',
            'data'    => $jurnal->fresh(),
        ]);
    }

    /**
     * DELETE /api/jurnal/{jurnal}
     */
    public function destroy(Jurnal $jurnal)
    {
        $jurnal->delete();

        return response()->json(['message' => 'Entri jurnal berhasil dihapus.']);
    }
}
