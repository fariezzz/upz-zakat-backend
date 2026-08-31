<?php

namespace Database\Seeders;

use App\Models\Mustahik;
use App\Models\Muzakki;
use App\Models\ProgramPenyaluran;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $muzakkis = Muzakki::all();
        $mustahiks = Mustahik::all();
        $programs = ProgramPenyaluran::where('status', 'aktif')->get();

        if ($muzakkis->isEmpty() || $programs->isEmpty()) {
            return;
        }

        // 1. Transaksi Pengumpulan (Zakat & Infaq dari Muzakki)
        $kategoris = ['Zakat Profesi', 'Zakat Maal', 'Infaq', 'Sedekah', 'Zakat Fitrah'];
        $metodes = ['Transfer Bank', 'Potong Gaji', 'QRIS', 'Tunai'];

        $count = 0;
        foreach ($muzakkis->take(15) as $muzakki) {
            $count++;
            $nominal = rand(2, 20) * 100000; // 200rb - 2jt
            $kategori = $kategoris[array_rand($kategoris)];
            $metode = $metodes[array_rand($metodes)];
            $program = rand(0, 1) ? $programs->where('tahun', 2026)->random() : null;

            Transaksi::create([
                'kode'        => 'TRX-MASUK-' . str_pad($count, 4, '0', STR_PAD_LEFT),
                'jenis'       => 'masuk',
                'kategori'    => $kategori,
                'deskripsi'   => 'Penerimaan ' . $kategori . ' dari ' . $muzakki->nama . ($program ? ' untuk ' . $program->nama : ''),
                'nominal'     => $nominal,
                'metode'      => $metode,
                'tahun'       => 2026,
                'bulan'       => rand(1, 8),
                'muzakki_id'  => $muzakki->id,
                'program_id'  => $program?->id,
                'created_at'  => now()->subDays(rand(1, 60)),
            ]);
        }

        // 2. Transaksi Penyaluran (Penyaluran ke Mustahik untuk Program)
        $programs2026 = $programs->where('tahun', 2026);
        $keluarCount = 0;

        foreach ($programs2026 as $prg) {
            $sampleMustahiks = $mustahiks->random(min(3, $mustahiks->count()));
            foreach ($sampleMustahiks as $mst) {
                $keluarCount++;
                $nominalSalur = rand(10, 50) * 100000; // 1jt - 5jt

                Transaksi::create([
                    'kode'        => 'TRX-KELUAR-' . str_pad($keluarCount, 4, '0', STR_PAD_LEFT),
                    'jenis'       => 'keluar',
                    'kategori'    => 'Penyaluran Program',
                    'deskripsi'   => 'Penyaluran ' . $prg->nama . ' kepada ' . $mst->nama . ' (' . $mst->kategori . ')',
                    'nominal'     => $nominalSalur,
                    'metode'      => 'Transfer Bank',
                    'tahun'       => 2026,
                    'bulan'       => rand(1, 8),
                    'mustahik_id' => $mst->id,
                    'program_id'  => $prg->id,
                    'created_at'  => now()->subDays(rand(1, 45)),
                ]);
            }
        }
    }
}

