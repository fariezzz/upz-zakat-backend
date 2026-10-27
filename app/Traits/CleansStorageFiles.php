<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait CleansStorageFiles
{
    /**
     * Hapus berkas fisik lokal dari storage disk publik jika ada
     */
    public static function deleteStorageFile(?string $url): void
    {
        if (empty($url) || !is_string($url)) {
            return;
        }

        // Abaikan jika data URI (base64) atau blob URL
        if (str_starts_with($url, 'data:') || str_starts_with($url, 'blob:')) {
            return;
        }

        $path = $url;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?? '';
        }

        // Bersihkan prefix storage (misal /api/storage/ atau /storage/)
        if (str_contains($path, '/storage/')) {
            $path = substr($path, strpos($path, '/storage/') + strlen('/storage/'));
        }

        $path = ltrim($path, '/');

        if (!empty($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
