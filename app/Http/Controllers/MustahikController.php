<?php

namespace App\Http\Controllers;

use App\Models\Mustahik;
use Illuminate\Http\Request;

class MustahikController extends Controller
{
    /**
     * GET /api/mustahik
     * Query params: search, status, kategori, per_page, page
     */
    public function index(Request $request)
    {
        $query = Mustahik::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'ilike', "%{$search}%")
                  ->orWhere('nik', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('alamat', 'ilike', "%{$search}%")
                  ->orWhere('kategori', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($kategori = $request->query('kategori')) {
            $query->where('kategori', $kategori);
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $data    = $query->orderByDesc('created_at')->paginate($perPage);

        $totalKontak = Mustahik::whereNotNull('no_hp')->where('no_hp', '!=', '')->count();

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'total_kontak' => $totalKontak,
            ],
        ]);
    }

    /**
     * POST /api/mustahik
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'     => 'required|string|max:100',
            'nik'      => 'nullable|string|max:30',
            'email'    => 'nullable|email|max:100',
            'no_hp'    => 'nullable|string|max:20',
            'alamat'   => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:100',
        ]);

        $validated['status'] = 'aktif';

        $mustahik = Mustahik::create($validated);
        return response()->json($mustahik, 201);
    }

    /**
     * PUT /api/mustahik/{id}
     */
    public function update(Request $request, Mustahik $mustahik)
    {
        $validated = $request->validate([
            'nama'     => 'sometimes|required|string|max:100',
            'nik'      => 'nullable|string|max:30',
            'email'    => 'nullable|email|max:100',
            'no_hp'    => 'nullable|string|max:20',
            'alamat'   => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'status'   => 'in:aktif,tidak_aktif',
        ]);

        $mustahik->update($validated);
        return response()->json($mustahik);
    }

    /**
     * DELETE /api/mustahik/{id}
     */
    public function destroy(Mustahik $mustahik)
    {
        $mustahik->delete();
        return response()->json(['message' => 'Mustahik berhasil dihapus.']);
    }
}
