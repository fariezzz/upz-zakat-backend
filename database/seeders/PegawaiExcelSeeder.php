<?php

namespace Database\Seeders;

use App\Models\Muzakki;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PegawaiExcelSeeder extends Seeder
{
    /**
     * Jalankan proses import data pegawai dari file Excel ke tabel muzakki.
     */
    public function run(): void
    {
        $filePath = base_path('../upz-zakat-frontend/src/assets/data/pegawai.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("File Excel tidak ditemukan: {$filePath}");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Data Pegawai');

        if (!$sheet) {
            $this->command->error("Sheet 'Data Pegawai' tidak ditemukan.");
            return;
        }

        $highestRow = $sheet->getHighestRow();
        $imported = 0;
        $skipped  = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            // Baca NIP sebagai string angka mentah dari cell
            $nipRaw = $sheet->getCell("A{$row}")->getValue();
            $nip    = $nipRaw !== null ? number_format((float) $nipRaw, 0, '', '') : null;
            // Fallback: jika kosong setelah format, gunakan format asli
            if (empty($nip) && $nipRaw !== null) {
                $nip = (string) $nipRaw;
            }

            $namaRaw = trim($sheet->getCell("B{$row}")->getValue() ?? '');
            $nama = $this->formatNama($namaRaw);
            $gajiPokok  = (int) ($sheet->getCell("C{$row}")->getValue() ?? 0);
            $tunjFung   = (int) ($sheet->getCell("D{$row}")->getValue() ?? 0);
            $tunjProf   = (int) ($sheet->getCell("E{$row}")->getValue() ?? 0);
            $tunjJab    = (int) ($sheet->getCell("F{$row}")->getValue() ?? 0);
            $tunjDosen  = (int) ($sheet->getCell("G{$row}")->getValue() ?? 0);
            $tunjTukin  = (int) ($sheet->getCell("H{$row}")->getValue() ?? 0);
            $status     = trim($sheet->getCell("I{$row}")->getValue() ?? '');
            $totalPeng  = (int) ($sheet->getCell("J{$row}")->getValue() ?? 0);

            // Skip baris kosong
            if (empty($nama)) {
                $skipped++;
                continue;
            }

            // Bangun kesepakatan_zakat dari komponen penghasilan
            $kesepakatan = [];
            if ($gajiPokok > 0) {
                $kesepakatan[] = ['komponen' => 'Gaji Pokok', 'nominal' => $gajiPokok, 'zakat' => round($gajiPokok * 0.025)];
            }
            if ($tunjFung > 0) {
                $kesepakatan[] = ['komponen' => 'Tunjangan Fungsional', 'nominal' => $tunjFung, 'zakat' => round($tunjFung * 0.025)];
            }
            if ($tunjProf > 0) {
                $kesepakatan[] = ['komponen' => 'Tunjangan Profesi', 'nominal' => $tunjProf, 'zakat' => round($tunjProf * 0.025)];
            }
            if ($tunjJab > 0) {
                $kesepakatan[] = ['komponen' => 'Tunjangan Jabatan Struktural', 'nominal' => $tunjJab, 'zakat' => round($tunjJab * 0.025)];
            }
            if ($tunjDosen > 0) {
                $kesepakatan[] = ['komponen' => 'Tunjangan Dosen Tugas Tambahan', 'nominal' => $tunjDosen, 'zakat' => round($tunjDosen * 0.025)];
            }
            if ($tunjTukin > 0) {
                $kesepakatan[] = ['komponen' => 'Tunjangan Kinerja (Tukin)', 'nominal' => $tunjTukin, 'zakat' => round($tunjTukin * 0.025)];
            }

            $totalZakat = array_sum(array_column($kesepakatan, 'zakat'));

            // Tentukan pekerjaan dari gelar di nama
            $pekerjaan = $this->detectPekerjaan($nama);

            // Tentukan tipe: Muzakki = terdaftar, Non Muzakki = juga dimasukkan sebagai terdaftar
            Muzakki::updateOrCreate(
                ['nama' => $nama],
                [
                    'nama'              => $nama,
                    'nik'               => null,
                    'nip'               => $nip,
                    'jenis_kelamin'     => null,
                    'tempat_lahir'      => null,
                    'tanggal_lahir'     => null,
                    'pekerjaan'         => $pekerjaan,
                    'alamat_lengkap'    => null,
                    'email'             => null,
                    'no_hp'             => null,
                    'kategori'          => 'Dosen & Staf UNSIL',
                    'unit_kerja'        => 'Civitas Akademika UNSIL',
                    'jenis_zakat'       => $status === 'Muzakki' ? 'Zakat Penghasilan' : null,
                    'frekuensi'         => $status === 'Muzakki' ? 'bulanan' : null,
                    'nominal'           => $status === 'Muzakki' ? $totalZakat : null,
                    'metode_pembayaran' => $status === 'Muzakki' ? 'Potong Gaji' : null,
                    'pilihan_bank'      => null,
                    'pilihan_ewallet'   => null,
                    'kesepakatan_zakat' => $status === 'Muzakki' ? $kesepakatan : null,
                    'tipe_muzakki'      => 'terdaftar',
                ]
            );

            $imported++;
        }

        $this->command->info("Import selesai: {$imported} pegawai dimasukkan, {$skipped} baris dilewati.");
    }

    /**
     * Format nama menjadi Proper Case,
     * tetapi gelar tetap menggunakan format gelar yang benar.
     */
    private function formatNama(string $nama): string
    {
        $nama = trim($nama);

        if ($nama === '') {
            return '';
        }

        // Pisahkan bagian nama dengan bagian gelar setelah koma.
        $parts = array_map('trim', explode(',', $nama));

        // Bagian pertama adalah nama + kemungkinan gelar depan
        $namaUtama = array_shift($parts);

        // Proper Case untuk nama utama
        $namaUtama = ucwords(strtolower($namaUtama));

        // Daftar gelar depan yang ingin distandarkan
        $gelarDepan = [
            'Prof.'  => 'Prof.',
            'Dr.'    => 'Dr.',
            'Drs.'   => 'Drs.',
            'Dra.'   => 'Dra.',
            'Ir.'    => 'Ir.',
            'H.'     => 'H.',
            'Hj.'    => 'Hj.',
            'KH.'    => 'KH.',
        ];

        // Perbaiki kapitalisasi gelar depan
        foreach ($gelarDepan as $gelar => $format) {
            $namaUtama = preg_replace(
                '/\b' . preg_quote($gelar, '/') . '\b/i',
                $format,
                $namaUtama
            );
        }

        // Daftar gelar belakang
        $gelarBelakang = [
            'S.Pd.'    => 'S.Pd.',
            'S.Pd'     => 'S.Pd.',
            'S.T.'     => 'S.T.',
            'S.T'      => 'S.T.',
            'S.E.'     => 'S.E.',
            'S.E'      => 'S.E.',
            'S.Ag.'    => 'S.Ag.',
            'S.Ag'     => 'S.Ag.',
            'S.Sos.'   => 'S.Sos.',
            'S.Sos'    => 'S.Sos.',
            'S.Kom.'   => 'S.Kom.',
            'S.Kom'    => 'S.Kom.',
            'S.Si.'    => 'S.Si.',
            'S.Si'     => 'S.Si.',
            'S.H.'     => 'S.H.',
            'S.H'      => 'S.H.',
            'S.KM.'    => 'S.KM.',
            'S.KM'     => 'S.KM.',
            'M.Pd.'    => 'M.Pd.',
            'M.Pd'     => 'M.Pd.',
            'M.T.'     => 'M.T.',
            'M.T'      => 'M.T.',
            'M.Si.'    => 'M.Si.',
            'M.Si'     => 'M.Si.',
            'M.Ag.'    => 'M.Ag.',
            'M.Ag'     => 'M.Ag.',
            'M.Ak.'    => 'M.Ak.',
            'M.Ak'     => 'M.Ak.',
            'M.E.'     => 'M.E.',
            'M.E'      => 'M.E.',
            'M.Sc.'    => 'M.Sc.',
            'M.Sc'     => 'M.Sc.',
            'M.Kom.'   => 'M.Kom.',
            'M.Kom'    => 'M.Kom.',
            'M.Kes.'   => 'M.Kes.',
            'M.Kes'    => 'M.Kes.',
            'M.Hum.'   => 'M.Hum.',
            'M.Hum'    => 'M.Hum.',
            'M.I.Kom.' => 'M.I.Kom.',
            'M.I.Kom'  => 'M.I.Kom.',
            'M.M.'     => 'M.M.',
            'M.M'      => 'M.M.',
            'M.S.'     => 'M.S.',
            'M.S'      => 'M.S.',
            'Ph.D.'    => 'Ph.D.',
            'Ph.D'     => 'Ph.D.',
        ];

        $hasilGelar = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            $partUpper = strtoupper($part);

            $found = false;

            foreach ($gelarBelakang as $gelar => $format) {
                if (strtoupper($gelar) === $partUpper) {
                    $hasilGelar[] = $format;
                    $found = true;
                    break;
                }
            }

            // Kalau bukan gelar yang dikenal,
            // jangan dipaksakan menjadi lowercase.
            if (!$found) {
                $hasilGelar[] = $part;
            }
        }

        return $namaUtama .
            (!empty($hasilGelar) ? ', ' . implode(', ', $hasilGelar) : '');
    }

    /**
     * Deteksi pekerjaan berdasarkan gelar di nama.
     */
    private function detectPekerjaan(string $nama): string
    {
        $gelarDosen = [
            'Prof.',
            'Dr.',
            'Ir.',
            'Drs.',
            'Dra.',
            'M.Pd',
            'M.T',
            'M.Si',
            'M.Ag',
            'M.Ak',
            'M.E.',
            'M.Sc',
            'M.Kom',
            'M.Kes',
            'M.Hum',
            'M.I.Kom',
            'S.Pd',
            'S.T.',
            'S.E.',
            'S.Ag',
            'S.EI',
            'S.Sos',
            'S.Kom',
            'S.Si',
            'S.H.',
            'S.Ked',
            'S.KM',
            'Ph.D',
            'MP.',
            'M.S.',
            'M.M.',
        ];

        foreach ($gelarDosen as $gelar) {
            if (stripos($nama, $gelar) !== false) {
                return 'Dosen';
            }
        }

        return 'Staf';
    }
}
