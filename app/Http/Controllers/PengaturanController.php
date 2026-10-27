<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PengaturanController extends Controller
{
    /**
     * Data default jika database belum memiliki record
     */
    protected function getDefaults(): array
    {
        return [
            'beranda' => [
                'hero' => [
                    'badge' => 'Transparan • Amanah • Profesional',
                    'judul' => 'Menunaikan Zakat, Membersamai Umat',
                    'deskripsi' => 'Unit Pengumpul Zakat (UPZ) Universitas Siliwangi hadir untuk memudahkan sivitas akademika dan masyarakat dalam menunaikan zakat, infak, dan sedekah secara aman, transparan, dan terpercaya.',
                    'foto' => null,
                ],
                'kutipan' => [
                    'isi' => 'Ambillah zakat dari sebagian harta mereka, dengan zakat itu kamu membersihkan dan mensucikan mereka.',
                    'sumber' => 'QS. At-Taubah: 103',
                ],
                'infoTambahan' => [
                    'taglineBanner' => 'Bersama zakat, kita bangun kebaikan dan keberkahan.',
                    'labelMuzakki' => 'Muzakki Terdaftar',
                    'labelMustahik' => 'Mustahik Terbantu',
                ],
            ],
            'kontak' => [
                'kantor' => [
                    'namaGedung' => 'Gedung Rektorat UNSIL Lantai 1',
                    'alamat' => 'Jl. Siliwangi No. 24, Kahuripan, Kec. Tawang, Kota Tasikmalaya, Jawa Barat 46115',
                    'whatsapp' => '081992300590',
                    'email' => 'upz@unsil.ac.id',
                    'googleMapsUrl' => 'https://maps.google.com/?q=Universitas+Siliwangi',
                ],
                'jamOperasional' => [
                    'hariKerja' => 'Senin – Jumat',
                    'jamKerja' => '09.00 – 16.00 WIB',
                    'hariLibur' => 'Sabtu, Minggu, & Hari Libur',
                    'jamLibur' => 'Tutup',
                ],
                'sosialMedia' => [
                    'facebook' => [
                        'handle' => '@upzzakatunsil',
                        'url' => 'https://facebook.com/upzzakatunsil',
                    ],
                    'instagram' => [
                        'handle' => '@upzzakat.unsil',
                        'url' => 'https://instagram.com/upzzakat.unsil',
                    ],
                    'twitter' => [
                        'handle' => '@upzzakatunsil',
                        'url' => 'https://twitter.com/upzzakatunsil',
                    ],
                    'youtube' => [
                        'handle' => 'UPZ Zakat Unsil',
                        'url' => 'https://youtube.com/@upzzakatunsil',
                    ],
                ],
            ],
            'footer' => [
                'brand' => [
                    'nama' => 'UPZ Unsil',
                    'subnama' => 'Universitas Siliwangi',
                ],
                'deskripsi' => 'Unit Pengumpul Zakat (UPZ) Universitas Siliwangi berkomitmen dalam mengelola dan menyalurkan zakat, infak, dan sedekah secara amanah, transparan, dan tepat sasaran.',
                'kontak' => [
                    'alamat' => 'Jl. Siliwangi No. 24, Kahuripan, Kec. Tawang, Kota Tasikmalaya, Jawa Barat 46115',
                    'whatsapp' => '081992300590',
                    'email' => 'upz@unsil.ac.id',
                ],
                'sosialMedia' => [
                    'instagram' => 'https://instagram.com/upzzakat.unsil',
                    'facebook' => 'https://facebook.com/upzzakatunsil',
                    'youtube' => 'https://youtube.com/@upzzakatunsil',
                ],
                'tagline' => 'Amanah • Transparan • Tepat Sasaran',
            ],
        ];
    }

    /**
     * GET /api/public/pengaturan
     * Mengambil semua konfigurasi publik (beranda, kontak, footer) sekaligus
     */
    public function publicAll(): JsonResponse
    {
        $defaults = $this->getDefaults();
        $stored = Pengaturan::pluck('value', 'key')->toArray();

        $result = [];
        foreach ($defaults as $k => $def) {
            $result[$k] = isset($stored[$k]) ? array_replace_recursive($def, $stored[$k]) : $def;
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * GET /api/public/pengaturan/{key}
     * Mengambil konfigurasi spesifik (publik)
     */
    public function publicShow(string $key): JsonResponse
    {
        $defaults = $this->getDefaults();
        $record = Pengaturan::where('key', $key)->first();

        if ($record && !empty($record->value)) {
            $defaultVal = $defaults[$key] ?? [];
            $data = is_array($defaultVal) ? array_replace_recursive($defaultVal, $record->value) : $record->value;
        } else {
            $data = $defaults[$key] ?? null;
        }

        if ($data === null) {
            return response()->json([
                'success' => false,
                'message' => "Pengaturan untuk key '{$key}' tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'key' => $key,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/pengaturan/{key}
     * Mengambil konfigurasi untuk admin (auth:sanctum)
     */
    public function show(string $key): JsonResponse
    {
        return $this->publicShow($key);
    }

    /**
     * POST/PUT /api/pengaturan/{key}
     * Menyimpan/memperbarui konfigurasi (auth:sanctum)
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $value = $request->input('value', $request->all());

        if (empty($value) || (!isset($value['hero']) && !isset($value['kantor']) && !isset($value['brand']) && !isset($value['deskripsi']))) {
            $rawAll = $request->except(['_token', '_method']);
            if (!empty($rawAll)) {
                $value = $rawAll;
            }
        }

        $pengaturan = Pengaturan::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        return response()->json([
            'success' => true,
            'message' => "Pengaturan {$key} berhasil diperbarui.",
            'key' => $key,
            'data' => $pengaturan->value,
        ]);
    }
}
