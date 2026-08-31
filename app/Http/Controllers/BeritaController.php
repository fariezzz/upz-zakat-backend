<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BeritaController extends Controller
{
    /**
     * GET /api/berita
     * List berita untuk admin dashboard (semua status: draft/published).
     */
    public function index(Request $request)
    {
        $query = Berita::with('author:id,name')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('judul', 'like', "%{$search}%")
                          ->orWhere('kategori', 'like', "%{$search}%")
                          ->orWhere('ringkasan', 'like', "%{$search}%");
                });
            })
            ->when($request->kategori, fn($q, $kat) => $q->where('kategori', $kat))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at');

        $perPage = min((int) $request->get('per_page', 10), 100);
        $data = $query->paginate($perPage);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    /**
     * POST /api/berita
     * Membuat berita baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'        => 'required|string|max:255',
            'kategori'     => 'required|string|max:100',
            'ringkasan'    => 'nullable|string|max:500',
            'konten'       => 'required|string',
            'gambar'       => 'nullable|string', // URL gambar atau base64/path
            'status'       => 'required|in:draft,published',
            'published_at' => 'nullable|date',
        ]);

        // Generate unique slug
        $baseSlug = Str::slug($validated['judul']);
        $slug = $baseSlug;
        $count = 1;
        while (Berita::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-" . ($count++);
        }

        $publishedAt = null;
        if ($validated['status'] === 'published') {
            $publishedAt = !empty($validated['published_at']) 
                ? $validated['published_at'] 
                : now();
        }

        $berita = Berita::create([
            'judul'        => $validated['judul'],
            'slug'         => $slug,
            'kategori'     => $validated['kategori'],
            'ringkasan'    => $validated['ringkasan'] ?? Str::limit(strip_tags($validated['konten']), 160),
            'konten'       => $validated['konten'],
            'gambar'       => $validated['gambar'] ?? null,
            'status'       => $validated['status'],
            'published_at' => $publishedAt,
            'author_id'    => $request->user()?->id,
        ]);

        $berita->load('author:id,name');

        return response()->json([
            'message' => 'Berita berhasil dibuat.',
            'data'    => $berita,
        ], 201);
    }

    /**
     * GET /api/berita/{id}
     * Detail berita untuk view/edit di admin.
     */
    public function show(Berita $berita)
    {
        $berita->load('author:id,name');
        return response()->json(['data' => $berita]);
    }

    /**
     * PUT /api/berita/{id}
     * Update berita yang ada.
     */
    public function update(Request $request, Berita $berita)
    {
        $validated = $request->validate([
            'judul'        => 'sometimes|required|string|max:255',
            'kategori'     => 'sometimes|required|string|max:100',
            'ringkasan'    => 'nullable|string|max:500',
            'konten'       => 'sometimes|required|string',
            'gambar'       => 'nullable|string',
            'status'       => 'sometimes|required|in:draft,published',
            'published_at' => 'nullable|date',
        ]);

        if (isset($validated['judul']) && $validated['judul'] !== $berita->judul) {
            $baseSlug = Str::slug($validated['judul']);
            $slug = $baseSlug;
            $count = 1;
            while (Berita::where('slug', $slug)->where('id', '!=', $berita->id)->exists()) {
                $slug = "{$baseSlug}-" . ($count++);
            }
            $berita->slug = $slug;
        }

        if (isset($validated['status'])) {
            if ($validated['status'] === 'published' && !$berita->published_at && empty($validated['published_at'])) {
                $berita->published_at = now();
            } elseif (isset($validated['published_at'])) {
                $berita->published_at = $validated['published_at'];
            }
        }

        if (isset($validated['ringkasan'])) {
            $berita->ringkasan = $validated['ringkasan'];
        } elseif (isset($validated['konten']) && empty($berita->ringkasan)) {
            $berita->ringkasan = Str::limit(strip_tags($validated['konten']), 160);
        }

        $berita->fill(array_filter($validated, fn($key) => !in_array($key, ['slug', 'ringkasan', 'published_at']), ARRAY_FILTER_USE_KEY));
        $berita->save();

        $berita->load('author:id,name');

        return response()->json([
            'message' => 'Berita berhasil diperbarui.',
            'data'    => $berita,
        ]);
    }

    /**
     * DELETE /api/berita/{id}
     * Hapus berita.
     */
    public function destroy(Berita $berita)
    {
        $berita->delete();
        return response()->json(['message' => 'Berita berhasil dihapus.']);
    }

    /**
     * POST /api/berita/upload-image
     * Helper untuk upload gambar dari editor / thumbnail.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120', // maks 5MB
        ]);

        $file = $request->file('image');

        // Jika Cloudinary terkonfigurasi, simpan langsung ke CDN Cloudinary
        if (env('CLOUDINARY_CLOUD_NAME') && env('CLOUDINARY_API_KEY') && env('CLOUDINARY_API_SECRET')) {
            try {
                $uploadedFile = $file->storeOnCloudinary('berita');
                return response()->json([
                    'message' => 'Gambar berhasil diunggah ke cloud.',
                    'url'     => $uploadedFile->getSecurePath(),
                    'path'    => $uploadedFile->getPublicId(),
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Cloudinary upload error: ' . $e->getMessage());
            }
        }

        // Simpan ke disk public dan kembalikan relative path agar portabel di semua environment
        $path = $file->store('berita', 'public');
        $relativeUrl = '/storage/' . $path;

        return response()->json([
            'message' => 'Gambar berhasil diunggah.',
            'url'     => $relativeUrl,
            'path'    => $path,
        ]);
    }

    /**
     * GET /api/public/berita
     * Endpoint publik untuk daftar berita yang sudah published (disiapkan untuk tahap berikutnya).
     */
    public function publicList(Request $request)
    {
        $query = Berita::where('status', 'published')
            ->with('author:id,name')
            ->when($request->kategori, fn($q, $kat) => $q->where('kategori', $kat))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('judul', 'like', "%{$search}%")
                          ->orWhere('ringkasan', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('published_at');

        $perPage = min((int) $request->get('per_page', 9), 50);
        $data = $query->paginate($perPage);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    /**
     * GET /api/public/berita/{idOrSlug}
     * Endpoint publik untuk detail berita published.
     */
    public function publicDetail($idOrSlug)
    {
        $berita = Berita::where('status', 'published')
            ->where(function ($q) use ($idOrSlug) {
                $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
            })
            ->with('author:id,name')
            ->firstOrFail();

        return response()->json(['data' => $berita]);
    }
}
