<?php

namespace App\Http\Controllers;

use App\Models\Muzakki;
use Illuminate\Http\Request;

class MuzakkiController extends Controller
{
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
