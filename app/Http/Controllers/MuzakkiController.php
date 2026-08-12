<?php

namespace App\Http\Controllers;

use App\Models\Muzakki;
use Illuminate\Http\Request;

class MuzakkiController extends Controller
{
    /**
     * GET /api/muzakki
     * Query params: search, status, per_page, page
     */
    public function index(Request $request)
    {
        $query = Muzakki::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('unit_kerja', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $data = $query->withCount('transaksi')->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data'  => $data->items(),
            'meta'  => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
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

        $data = Muzakki::where('status', 'aktif')
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
            'email'      => 'nullable|email|max:100',
            'no_hp'      => 'nullable|string|max:20',
            'unit_kerja' => 'nullable|string|max:100',
            'status'     => 'in:aktif,tidak_aktif',
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
            'nama'       => 'sometimes|required|string|max:100',
            'email'      => 'nullable|email|max:100',
            'no_hp'      => 'nullable|string|max:20',
            'unit_kerja' => 'nullable|string|max:100',
            'status'     => 'in:aktif,tidak_aktif',
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
