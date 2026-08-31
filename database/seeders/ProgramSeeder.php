<?php

namespace Database\Seeders;

use App\Models\ProgramPenyaluran;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programTemplates = [
            [
                'kode_prefix' => 'PRG-BSW',
                'nama'        => 'Beasiswa Mahasiswa Mustahik',
                'deskripsi'   => 'Program beasiswa untuk mahasiswa Unsil yang termasuk kategori mustahik dengan keterbatasan ekonomi.',
            ],
            [
                'kode_prefix' => 'PRG-YTM',
                'nama'        => 'Santunan Yatim & Dhuafa',
                'deskripsi'   => 'Santunan rutin bulanan untuk anak yatim dan kaum dhuafa di lingkungan sekitar kampus Unsil.',
            ],
            [
                'kode_prefix' => 'PRG-KSH',
                'nama'        => 'Bantuan Kesehatan',
                'deskripsi'   => 'Bantuan biaya kesehatan dan pengobatan bagi mustahik yang membutuhkan layanan medis.',
            ],
            [
                'kode_prefix' => 'PRG-UMK',
                'nama'        => 'Bantuan UMKM Mustahik',
                'deskripsi'   => 'Modal usaha dan pendampingan untuk mustahik yang ingin berwirausaha agar mandiri secara ekonomi.',
            ],
        ];

        $targets = [
            2024 => [300000000, 250000000, 150000000, 120000000],
            2025 => [320000000, 280000000, 170000000, 150000000],
            2026 => [350000000, 300000000, 190000000, 165000000],
        ];

        foreach ([2024, 2025, 2026] as $year) {
            foreach ($programTemplates as $index => $tmpl) {
                ProgramPenyaluran::updateOrCreate(
                    ['kode' => $tmpl['kode_prefix'] . '-' . $year],
                    [
                        'nama'           => $tmpl['nama'],
                        'deskripsi'      => $tmpl['deskripsi'],
                        'target_nominal' => $targets[$year][$index],
                        'status'         => 'aktif',
                        'tahun'          => $year,
                    ]
                );
            }
        }
    }
}

