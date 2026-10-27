<?php

namespace App\Http\Controllers;

use App\Models\Pengurus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PengurusController extends Controller
{
    /**
     * Data susunan pengurus default resmi
     */
    public static function getDefaultData(): array
    {
        return [
            [
                'nama' => 'Dr. Ir. Acep Irham Gufroni, M.Eng.',
                'jabatan' => 'Ketua UPZ',
                'urutan' => 1,
                'foto' => null,
            ],
            [
                'nama' => 'Dr. Joni, S.E.I., M.E.Sy.',
                'jabatan' => 'Wakil Ketua UPZ',
                'urutan' => 2,
                'foto' => null,
            ],
            [
                'nama' => 'Dr. Dita Agustian, M.Pd.',
                'jabatan' => 'Sekretaris UPZ',
                'urutan' => 3,
                'foto' => null,
            ],
            [
                'nama' => 'Ir. Randi Rizal, Ph.D.',
                'jabatan' => 'Bendahara UPZ',
                'urutan' => 4,
                'foto' => null,
            ],
            [
                'nama' => 'Riza Nurdiana, S.E.',
                'jabatan' => 'Administrasi',
                'urutan' => 5,
                'foto' => null,
            ],
        ];
    }

    /**
     * GET /api/public/pengurus (PUBLIC)
     */
    public function publicList(): JsonResponse
    {
        $list = Pengurus::orderBy('urutan', 'asc')->get();

        // Jika database belum memiliki data pengurus, inisialisasi dengan data default
        if ($list->isEmpty()) {
            foreach (self::getDefaultData() as $item) {
                Pengurus::create($item);
            }
            $list = Pengurus::orderBy('urutan', 'asc')->get();
        }

        return response()->json([
            'success' => true,
            'data' => $list,
        ]);
    }

    /**
     * GET /api/pengurus (ADMIN)
     */
    public function index(): JsonResponse
    {
        return $this->publicList();
    }

    /**
     * POST /api/pengurus (ADMIN)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'jabatan' => 'required|string|max:100',
            'urutan' => 'nullable|integer',
            'foto' => 'nullable',
        ]);

        $fotoUrl = null;

        // Cek jika foto diunggah sebagai file multipart
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('pengurus', $filename, 'public');
            $fotoUrl = '/api/storage/pengurus/' . $filename;
        } elseif (is_string($request->input('foto'))) {
            $fotoUrl = $request->input('foto');
        }

        $urutan = $request->filled('urutan')
            ? (int) $request->input('urutan')
            : (Pengurus::max('urutan') ?? 0) + 1;

        $pengurus = Pengurus::create([
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'urutan' => $urutan,
            'foto' => $fotoUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pengurus berhasil ditambahkan.',
            'data' => $pengurus,
        ], 201);
    }

    /**
     * POST/PUT /api/pengurus/{id} (ADMIN)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $pengurus = Pengurus::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'jabatan' => 'required|string|max:100',
            'urutan' => 'nullable|integer',
            'foto' => 'nullable',
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('pengurus', $filename, 'public');
            $pengurus->foto = '/api/storage/pengurus/' . $filename;
        } elseif ($request->has('foto') && is_string($request->input('foto')) && !empty($request->input('foto'))) {
            $pengurus->foto = $request->input('foto');
        }

        $pengurus->nama = $validated['nama'];
        $pengurus->jabatan = $validated['jabatan'];
        if ($request->has('urutan')) {
            $pengurus->urutan = (int) $validated['urutan'];
        }

        $pengurus->save();

        return response()->json([
            'success' => true,
            'message' => 'Data pengurus berhasil diperbarui.',
            'data' => $pengurus,
        ]);
    }

    /**
     * DELETE /api/pengurus/{id} (ADMIN)
     */
    public function destroy($id): JsonResponse
    {
        $pengurus = Pengurus::findOrFail($id);
        $pengurus->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pengurus berhasil dihapus.',
        ]);
    }

    /**
     * POST /api/pengurus/reset-default (ADMIN)
     */
    public function resetDefault(): JsonResponse
    {
        // Bersihkan asset foto fisik sebelum tabel di-reset
        foreach (Pengurus::all() as $item) {
            Pengurus::deleteStorageFile($item->foto);
        }

        Pengurus::truncate();

        foreach (self::getDefaultData() as $item) {
            Pengurus::create($item);
        }

        $list = Pengurus::orderBy('urutan', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Susunan pengurus berhasil dikembalikan ke default awal.',
            'data' => $list,
        ]);
    }
}
