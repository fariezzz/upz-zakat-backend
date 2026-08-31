<?php

namespace App\Http\Controllers;

use App\Models\Muzakki;
use App\Models\ZakatAgreementRequest;
use Illuminate\Http\Request;

class ZakatAgreementController extends Controller
{
    /**
     * POST /api/public/zakat-request
     * Muzakki mengajukan perubahan kesepakatan zakat (PUBLIC - tanpa auth).
     */
    public function publicStore(Request $request)
    {
        $validated = $request->validate([
            'muzakki_id'           => 'required|integer|exists:muzakki,id',
            'alasan'               => 'nullable|string|max:500',
            'perubahan_diajukan'   => 'required|array|min:1',
            'perubahan_diajukan.*.key'      => 'required|string',
            'perubahan_diajukan.*.jenis'    => 'required|string',
            'perubahan_diajukan.*.frekuensi'=> 'required|string',
            'perubahan_diajukan.*.nominal'  => 'required|numeric|min:1000',
        ]);

        $muzakki = Muzakki::findOrFail($validated['muzakki_id']);

        // Tolak jika masih ada request pending dari muzakki yang sama
        $existing = ZakatAgreementRequest::where('muzakki_id', $muzakki->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Anda masih memiliki permohonan perubahan kesepakatan yang sedang menunggu persetujuan admin. Silakan tunggu atau hubungi admin UPZ.',
            ], 422);
        }

        $req = ZakatAgreementRequest::create([
            'muzakki_id'          => $muzakki->id,
            'nama_muzakki'        => $muzakki->nama,
            'nip'                 => $muzakki->nip,
            'nik'                 => $muzakki->nik,
            'no_hp'               => $muzakki->no_hp,
            'perubahan_diajukan'  => $validated['perubahan_diajukan'],
            'kesepakatan_lama'    => $muzakki->kesepakatan_zakat,
            'alasan'              => $validated['alasan'] ?? null,
            'status'              => 'pending',
        ]);

        return response()->json([
            'message'    => 'Permohonan perubahan kesepakatan zakat berhasil diajukan. Admin UPZ akan segera meninjaunya.',
            'request_id' => $req->id,
        ], 201);
    }

    /**
     * GET /api/zakat-requests  (PROTECTED)
     * Admin melihat semua permintaan perubahan.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = ZakatAgreementRequest::with('muzakki:id,nama,nip,nik,no_hp,kategori')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at');

        $data = $query->paginate(min((int) $request->query('per_page', 15), 100));

        return response()->json([
            'data' => $data->map(fn($r) => [
                'id'                  => $r->id,
                'muzakki_id'          => $r->muzakki_id,
                'nama_muzakki'        => $r->nama_muzakki,
                'nip'                 => $r->nip,
                'nik'                 => $r->nik,
                'no_hp'               => $r->no_hp,
                'perubahan_diajukan'  => $r->perubahan_diajukan,
                'kesepakatan_lama'    => $r->kesepakatan_lama,
                'alasan'              => $r->alasan,
                'status'              => $r->status,
                'catatan_admin'       => $r->catatan_admin,
                'diproses_oleh'       => $r->diproses_oleh,
                'diproses_at'         => $r->diproses_at?->toISOString(),
                'created_at'          => $r->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'total'        => $data->total(),
            ],
            'pending_count' => ZakatAgreementRequest::where('status', 'pending')->count(),
        ]);
    }

    /**
     * GET /api/zakat-requests/pending-count  (PROTECTED)
     * Mengembalikan jumlah request yang masih pending (untuk badge notifikasi).
     */
    public function pendingCount()
    {
        return response()->json([
            'count' => ZakatAgreementRequest::where('status', 'pending')->count(),
        ]);
    }

    /**
     * PATCH /api/zakat-requests/{id}/approve  (PROTECTED)
     * Admin menyetujui request dan langsung update data kesepakatan muzakki.
     */
    public function approve(Request $request, $id)
    {
        $req = ZakatAgreementRequest::where('status', 'pending')->findOrFail($id);

        $validated = $request->validate([
            'catatan_admin' => 'nullable|string|max:255',
        ]);

        // Update kesepakatan_zakat di tabel muzakki
        $muzakki = $req->muzakki;
        if ($muzakki) {
            $perubahan = $req->perubahan_diajukan;
            $totalNominal = collect($perubahan)->sum('nominal');

            $muzakki->update([
                'kesepakatan_zakat' => $perubahan,
                'nominal'           => $totalNominal,
                'jenis_zakat'       => collect($perubahan)->pluck('jenis')->implode(', '),
            ]);
        }

        $req->update([
            'status'        => 'disetujui',
            'catatan_admin' => $validated['catatan_admin'] ?? null,
            'diproses_oleh' => $request->user()->id,
            'diproses_at'   => now(),
        ]);

        return response()->json([
            'message' => 'Permohonan disetujui dan data kesepakatan muzakki telah diperbarui.',
            'request' => $req->fresh()->load('muzakki'),
        ]);
    }

    /**
     * PATCH /api/zakat-requests/{id}/reject  (PROTECTED)
     * Admin menolak request.
     */
    public function reject(Request $request, $id)
    {
        $req = ZakatAgreementRequest::where('status', 'pending')->findOrFail($id);

        $validated = $request->validate([
            'catatan_admin' => 'nullable|string|max:255',
        ]);

        $req->update([
            'status'        => 'ditolak',
            'catatan_admin' => $validated['catatan_admin'] ?? null,
            'diproses_oleh' => $request->user()->id,
            'diproses_at'   => now(),
        ]);

        return response()->json([
            'message' => 'Permohonan ditolak.',
        ]);
    }
}
