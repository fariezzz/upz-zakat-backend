<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
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

        foreach ($settings as $key => $val) {
            Pengaturan::updateOrCreate(
                ['key' => $key],
                ['value' => $val]
            );
        }
    }
}
