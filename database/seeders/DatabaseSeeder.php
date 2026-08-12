<?php

namespace Database\Seeders;

use App\Models\Muzakki;
use App\Models\Mustahik;
use App\Models\ProgramPenyaluran;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ——————————————————————————————————
        // 1. Admin User
        // ——————————————————————————————————
        User::updateOrCreate(
            ['email' => 'admin@unsil.ac.id'],
            ['name' => 'Admin UPZ', 'password' => Hash::make('password')]
        );

        // ——————————————————————————————————
        // 2. Muzakki
        // ——————————————————————————————————
        $muzakkiData = [
            ['nama' => 'Dr. Ahmad Fauzi, M.Pd.',       'unit_kerja' => 'FKIP'],
            ['nama' => 'Prof. Siti Rahayu, Ph.D.',     'unit_kerja' => 'Fakultas Ekonomi'],
            ['nama' => 'Drs. Hendra Kusuma, M.Si.',    'unit_kerja' => 'Fakultas Hukum'],
            ['nama' => 'Ir. Budi Santoso, M.T.',       'unit_kerja' => 'Fakultas Teknik'],
            ['nama' => 'Dr. Dewi Lestari, M.Kes.',     'unit_kerja' => 'Fakultas Kesehatan'],
            ['nama' => 'Dra. Ratna Wijaya, M.Pd.',     'unit_kerja' => 'FKIP'],
            ['nama' => 'H. Supriadi, S.E., M.M.',      'unit_kerja' => 'Rektorat'],
            ['nama' => 'Dr. Rina Marlina, M.Si.',      'unit_kerja' => 'Fakultas Pertanian'],
            ['nama' => 'Drs. Agus Hermawan, M.Hum.',   'unit_kerja' => 'Fakultas Sastra'],
            ['nama' => 'Dr. Indra Permana, M.Kom.',    'unit_kerja' => 'Fakultas Ilmu Komputer'],
            ['nama' => 'Hj. Sari Kusumadewi, S.Pd.',   'unit_kerja' => 'FKIP'],
            ['nama' => 'Prof. Bambang Wijaya, Ph.D.',  'unit_kerja' => 'Fakultas Ekonomi'],
            ['nama' => 'Dra. Nani Suryani, M.Pd.',    'unit_kerja' => 'Rektorat'],
            ['nama' => 'Dr. Fajar Nugraha, M.T.',      'unit_kerja' => 'Fakultas Teknik'],
            ['nama' => 'Ir. Yuli Ratnasari, M.P.',     'unit_kerja' => 'Fakultas Pertanian'],
        ];
        $muzakkiIds = [];
        foreach ($muzakkiData as $data) {
            $m = Muzakki::firstOrCreate(['nama' => $data['nama']], array_merge($data, ['status' => 'aktif']));
            $muzakkiIds[] = $m->id;
        }

        // ——————————————————————————————————
        // 3. Mustahik
        // ——————————————————————————————————
        $mustahikData = [
            ['nama' => 'Budi Santoso',         'alamat' => 'Jl. Cigoong No. 12, Tasikmalaya',     'kategori' => 'Fakir Miskin', 'no_hp' => '085211110001'],
            ['nama' => 'Siti Aminah',           'alamat' => 'Jl. Empangsari No. 5, Tasikmalaya',  'kategori' => 'Fakir Miskin', 'no_hp' => '085211110002'],
            ['nama' => 'Ahmad Rifa\'i',         'alamat' => 'Jl. Singaparna No. 33, Tasikmalaya', 'kategori' => 'Gharim',       'no_hp' => '085211110003'],
            ['nama' => 'Hj. Neneng Kurniasih',  'alamat' => 'Jl. Panglima No. 7, Tasikmalaya',    'kategori' => 'Fakir Miskin', 'no_hp' => '085211110004'],
            ['nama' => 'Dede Supriatna',        'alamat' => 'Jl. Rahayu No. 21, Tasikmalaya',     'kategori' => 'Ibnu Sabil',   'no_hp' => '085211110005'],
            ['nama' => 'Yayah Rokayah',         'alamat' => 'Jl. Nagarasari No. 14, Tasikmalaya', 'kategori' => 'Fakir Miskin', 'no_hp' => '085211110006'],
            ['nama' => 'Ujang Hermawan',        'alamat' => 'Jl. Gunung Gede No. 3, Tasikmalaya', 'kategori' => 'Gharim',       'no_hp' => '085211110007'],
            ['nama' => 'Imas Sukaesih',         'alamat' => 'Jl. Tamansari No. 9, Tasikmalaya',   'kategori' => 'Fakir Miskin', 'no_hp' => '085211110008'],
            ['nama' => 'Rahmat Hidayat',        'alamat' => 'Jl. Cihideung No. 17, Tasikmalaya',  'kategori' => 'Muallaf',      'no_hp' => '085211110009'],
            ['nama' => 'Wati Nuraeni',          'alamat' => 'Jl. Cipedes No. 25, Tasikmalaya',    'kategori' => 'Fakir Miskin', 'no_hp' => '085211110010'],
            ['nama' => 'Asep Ridwan',           'alamat' => 'Jl. Cilembang No. 8, Tasikmalaya',   'kategori' => 'Fakir Miskin', 'no_hp' => '085211110011'],
            ['nama' => 'Tini Sumarni',          'alamat' => 'Jl. Sindangraja No. 2, Tasikmalaya', 'kategori' => 'Gharim',       'no_hp' => '085211110012'],
            ['nama' => 'Cecep Nugraha',         'alamat' => 'Jl. Sukalaya No. 11, Tasikmalaya',   'kategori' => 'Fakir Miskin', 'no_hp' => '085211110013'],
            ['nama' => 'Euis Fatimah',          'alamat' => 'Jl. Sukaasih No. 6, Tasikmalaya',    'kategori' => 'Muallaf',      'no_hp' => '085211110014'],
            ['nama' => 'Tatang Sopandi',        'alamat' => 'Jl. Sukahurip No. 30, Tasikmalaya',  'kategori' => 'Ibnu Sabil',   'no_hp' => '085211110015'],
            ['nama' => 'Lilis Nurjanah',        'alamat' => 'Jl. Cikunir No. 19, Tasikmalaya',    'kategori' => 'Fakir Miskin', 'no_hp' => '085211110016'],
            ['nama' => 'Dani Ramdani',          'alamat' => 'Jl. Tawang No. 44, Tasikmalaya',     'kategori' => 'Gharim',       'no_hp' => '085211110017'],
            ['nama' => 'Rini Kartini',          'alamat' => 'Jl. Sumelap No. 1, Tasikmalaya',     'kategori' => 'Fakir Miskin', 'no_hp' => '085211110018'],
            ['nama' => 'Heri Gunawan',          'alamat' => 'Jl. Gobras No. 38, Tasikmalaya',     'kategori' => 'Fakir Miskin', 'no_hp' => '085211110019'],
            ['nama' => 'Sri Wahyuni',           'alamat' => 'Jl. Dadaha No. 15, Tasikmalaya',     'kategori' => 'Muallaf',      'no_hp' => '085211110020'],
        ];
        $mustahikIds = [];
        foreach ($mustahikData as $data) {
            $m = Mustahik::firstOrCreate(['nama' => $data['nama']], array_merge($data, ['status' => 'aktif']));
            $mustahikIds[] = $m->id;
        }
        $mCount = count($mustahikIds);

        // ——————————————————————————————————
        // 4. Program Penyaluran (dibuat lebih dulu agar bisa dikaitkan ke transaksi)
        // ——————————————————————————————————
        $programTemplates = [
            ['kode_prefix' => 'PRG-BSW', 'nama' => 'Beasiswa Mahasiswa Mustahik',
             'deskripsi' => 'Program beasiswa untuk mahasiswa Unsil yang termasuk kategori mustahik dengan keterbatasan ekonomi.'],
            ['kode_prefix' => 'PRG-YTM', 'nama' => 'Santunan Yatim & Dhuafa',
             'deskripsi' => 'Santunan rutin bulanan untuk anak yatim dan kaum dhuafa di lingkungan sekitar kampus Unsil.'],
            ['kode_prefix' => 'PRG-KSH', 'nama' => 'Bantuan Kesehatan',
             'deskripsi' => 'Bantuan biaya kesehatan dan pengobatan bagi mustahik yang membutuhkan layanan medis.'],
            ['kode_prefix' => 'PRG-UMK', 'nama' => 'Bantuan UMKM Mustahik',
             'deskripsi' => 'Modal usaha dan pendampingan untuk mustahik yang ingin berwirausaha agar mandiri secara ekonomi.'],
        ];
        $targets = [
            2024 => [300000000, 250000000, 150000000, 120000000],
            2025 => [320000000, 280000000, 170000000, 150000000],
            2026 => [350000000, 300000000, 190000000, 165000000],
        ];

        $pby = []; // programsByYear: "tahun_nama" => id
        foreach ([2024, 2025, 2026] as $year) {
            foreach ($programTemplates as $index => $tmpl) {
                $p = ProgramPenyaluran::firstOrCreate(
                    ['kode' => $tmpl['kode_prefix'] . '-' . $year],
                    ['nama' => $tmpl['nama'], 'deskripsi' => $tmpl['deskripsi'],
                     'target_nominal' => $targets[$year][$index], 'status' => 'aktif', 'tahun' => $year]
                );
                $pby["{$year}_{$tmpl['nama']}"] = $p->id;
            }
        }

        // Helper: ambil program_id berdasarkan tahun dan nama program
        $pid = fn(int $tahun, string $nama): ?int => $pby["{$tahun}_{$nama}"] ?? null;

        // Alias nama program
        $BSW = 'Beasiswa Mahasiswa Mustahik';
        $YTM = 'Santunan Yatim & Dhuafa';
        $KSH = 'Bantuan Kesehatan';
        $UMK = 'Bantuan UMKM Mustahik';

        // ——————————————————————————————————
        // 5. Transaksi
        // ——————————————————————————————————
        $trxCounter = 1;

        // ── 2024 Masuk ──────────────────────────────────────────────────────
        $masuk2024 = [
            [1, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  22000000],
            [2, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  24000000],
            [3, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  22500000],
            [4, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  26000000],
            [5, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  27000000],
            [6, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',        7000000],
            [7, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',        7500000],
            [8, 'Sedekah',     'Penerimaan Sedekah - Donasi Online',          3000000],
            [9, 'Sedekah',     'Penerimaan Sedekah - Kotak Amal',             2500000],
            [10,'Infaq',       'Penerimaan Infaq - Kegiatan Ramadhan',         9000000],
            [11,'Dana Lainnya','Penerimaan Dana Sosial - Hibah',               5000000],
            [12,'Zakat',       'Penerimaan Zakat Maal - Akhir Tahun',         28000000],
        ];
        foreach ($masuk2024 as $i => [$bulan, $kat, $desc, $nom]) {
            Transaksi::firstOrCreate(['kode' => 'TRX-24M-' . str_pad($trxCounter++, 3, '0', STR_PAD_LEFT)], [
                'jenis' => 'masuk', 'kategori' => $kat, 'deskripsi' => $desc,
                'nominal' => $nom, 'metode' => 'Transfer Bank', 'tahun' => 2024, 'bulan' => $bulan,
                'muzakki_id' => $muzakkiIds[$i % count($muzakkiIds)],
            ]);
        }

        // ── 2024 Keluar (program_id eksplisit, mustahik berbeda tiap transaksi) ──
        // format: [bulan, desc, nominal, program, mustahik_index]
        $keluar2024 = [
            [3,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 1',   12000000, $BSW,  0],
            [3,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 1',   13000000, $BSW,  1],
            [6,  'Penyaluran Santunan Yatim & Dhuafa - Triwulan 1',    8000000, $YTM,  2],
            [6,  'Penyaluran Santunan Yatim & Dhuafa - Triwulan 2',    7000000, $YTM,  3],
            [6,  'Penyaluran Bantuan Kesehatan - Semester 1',           8000000, $KSH,  4],
            [9,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 2',   12000000, $BSW,  5],
            [9,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 2',   13000000, $BSW,  6],
            [12, 'Penyaluran Santunan Yatim & Dhuafa - Triwulan 3',    8000000, $YTM,  7],
            [12, 'Penyaluran Bantuan UMKM Mustahik - Batch 1',          5000000, $UMK,  8],
        ];
        foreach ($keluar2024 as [$bulan, $desc, $nom, $prog, $mIdx]) {
            Transaksi::firstOrCreate(['kode' => 'TRX-24K-' . str_pad($trxCounter++, 3, '0', STR_PAD_LEFT)], [
                'jenis' => 'keluar', 'kategori' => 'Penyaluran', 'deskripsi' => $desc,
                'nominal' => $nom, 'metode' => 'Transfer Bank', 'tahun' => 2024, 'bulan' => $bulan,
                'mustahik_id' => $mustahikIds[$mIdx % $mCount],
                'program_id'  => $pid(2024, $prog),
            ]);
        }

        // ── 2025 Masuk ──────────────────────────────────────────────────────
        $masuk2025 = [
            [1, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  25750000],
            [1, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',        4250000],
            [2, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  27000000],
            [2, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',        6000000],
            [2, 'Sedekah',     'Penerimaan Sedekah - Donasi Online',          3000000],
            [3, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  28000000],
            [3, 'Infaq',       'Penerimaan Infaq - Kegiatan Kampus',           9000000],
            [3, 'Sedekah',     'Penerimaan Sedekah - Ramadhan',               12000000],
            [4, 'Zakat',       'Penerimaan Zakat Fitrah - Idul Fitri',        35000000],
            [4, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',        18000000],
            [4, 'Sedekah',     'Penerimaan Sedekah - Donasi Online',           5000000],
            [5, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  25750000],
            [5, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',         8250000],
            [6, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  22000000],
            [6, 'Dana Lainnya','Penerimaan Dana Sosial - CSR',                10000000],
            [7, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  30000000],
            [7, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',         9000000],
            [7, 'Sedekah',     'Penerimaan Sedekah - Donasi Online',           4000000],
        ];
        foreach ($masuk2025 as $i => [$bulan, $kat, $desc, $nom]) {
            Transaksi::firstOrCreate(['kode' => 'TRX-25M-' . str_pad($trxCounter++, 3, '0', STR_PAD_LEFT)], [
                'jenis' => 'masuk', 'kategori' => $kat, 'deskripsi' => $desc,
                'nominal' => $nom, 'metode' => 'Transfer Bank', 'tahun' => 2025, 'bulan' => $bulan,
                'muzakki_id' => $muzakkiIds[$i % count($muzakkiIds)],
            ]);
        }

        // ── 2025 Keluar ──────────────────────────────────────────────────────
        $keluar2025 = [
            [3,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 1',   18000000, $BSW,  0],
            [3,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 1',   17000000, $BSW,  1],
            [4,  'Penyaluran Santunan Yatim & Dhuafa - Apr 2025',      8000000, $YTM,  2],
            [4,  'Penyaluran Santunan Lebaran Yatim & Dhuafa 2025',    7000000, $YTM,  3],
            [4,  'Penyaluran Bantuan Lebaran Mustahik 2025',            5000000, $YTM,  4],
            [5,  'Penyaluran Bantuan Kesehatan - Rawat Inap',           5000000, $KSH,  5],
            [5,  'Penyaluran Bantuan Kesehatan - Operasi',              3500000, $KSH,  6],
            [6,  'Penyaluran Bantuan UMKM Mustahik - Modal Awal',       6000000, $UMK,  7],
            [6,  'Penyaluran Bantuan UMKM Mustahik - Pengembangan',     4000000, $UMK,  8],
            [7,  'Penyaluran Santunan Yatim & Dhuafa - Jul 2025',       7500000, $YTM,  9],
            [9,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 2',   18000000, $BSW, 10],
            [9,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 2',   17000000, $BSW, 11],
        ];
        foreach ($keluar2025 as [$bulan, $desc, $nom, $prog, $mIdx]) {
            Transaksi::firstOrCreate(['kode' => 'TRX-25K-' . str_pad($trxCounter++, 3, '0', STR_PAD_LEFT)], [
                'jenis' => 'keluar', 'kategori' => 'Penyaluran', 'deskripsi' => $desc,
                'nominal' => $nom, 'metode' => 'Transfer Bank', 'tahun' => 2025, 'bulan' => $bulan,
                'mustahik_id' => $mustahikIds[$mIdx % $mCount],
                'program_id'  => $pid(2025, $prog),
            ]);
        }

        // ── 2026 Masuk ──────────────────────────────────────────────────────
        $masuk2026 = [
            [1, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  26000000],
            [1, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',        5000000],
            [2, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  28000000],
            [2, 'Sedekah',     'Penerimaan Sedekah - Donasi Online',          4500000],
            [3, 'Zakat',       'Penerimaan Zakat Fitrah - Idul Fitri',        38000000],
            [4, 'Infaq',       'Penerimaan Infaq - Kegiatan Ramadhan',        12000000],
            [5, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  27000000],
            [6, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',        6500000],
            [7, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',  29000000],
            [8, 'Zakat Fitrah','Pengumpulan Zakat Fitrah',                    10000000],
            [8, 'Sedekah',     'Pengumpulan Sedekah',                           800000],
        ];
        foreach ($masuk2026 as $i => [$bulan, $kat, $desc, $nom]) {
            Transaksi::firstOrCreate(['kode' => 'TRX-26M-' . str_pad($trxCounter++, 3, '0', STR_PAD_LEFT)], [
                'jenis' => 'masuk', 'kategori' => $kat, 'deskripsi' => $desc,
                'nominal' => $nom, 'metode' => 'Transfer Bank', 'tahun' => 2026, 'bulan' => $bulan,
                'muzakki_id' => $muzakkiIds[$i % count($muzakkiIds)],
            ]);
        }

        // ── 2026 Keluar — tiap program ≥3 penerima unik ──────────────────────
        $keluar2026 = [
            // Beasiswa Mahasiswa Mustahik — 4 penerima berbeda
            [2,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 1',    8000000, $BSW,  0],
            [2,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 1',    8000000, $BSW,  1],
            [2,  'Penyaluran Beasiswa Mahasiswa Mustahik - Sem. 1',    7000000, $BSW,  2],
            [8,  'Penyaluran Beasiswa Dhuafa - Sem. 2',                 7000000, $BSW,  3],
            // Santunan Yatim & Dhuafa — 4 penerima berbeda
            [3,  'Penyaluran Santunan Yatim & Dhuafa - Mar 2026',       5000000, $YTM,  4],
            [4,  'Penyaluran Santunan Lebaran Yatim & Dhuafa 2026',     6000000, $YTM,  5],
            [4,  'Penyaluran Bantuan Lebaran Mustahik 2026',             4000000, $YTM,  6],
            [7,  'Penyaluran Santunan Yatim & Dhuafa - Jul 2026',       4500000, $YTM,  7],
            // Bantuan Kesehatan — 3 penerima berbeda
            [5,  'Penyaluran Bantuan Kesehatan - Rawat Inap',            3500000, $KSH,  8],
            [6,  'Penyaluran Bantuan Kesehatan - Operasi',               4000000, $KSH,  9],
            [8,  'Penyaluran Bantuan Kesehatan - Pengobatan Umum',       2500000, $KSH, 10],
            // Bantuan UMKM Mustahik — 3 penerima berbeda
            [6,  'Penyaluran Bantuan UMKM Mustahik - Modal Awal',        5000000, $UMK, 11],
            [6,  'Penyaluran Bantuan UMKM Mustahik - Pengembangan',      4000000, $UMK, 12],
            [8,  'Penyaluran Bantuan UMKM Mustahik - Alat Produksi',     3000000, $UMK, 13],
        ];
        foreach ($keluar2026 as [$bulan, $desc, $nom, $prog, $mIdx]) {
            Transaksi::firstOrCreate(['kode' => 'TRX-26K-' . str_pad($trxCounter++, 3, '0', STR_PAD_LEFT)], [
                'jenis' => 'keluar', 'kategori' => 'Penyaluran', 'deskripsi' => $desc,
                'nominal' => $nom, 'metode' => 'Transfer Bank', 'tahun' => 2026, 'bulan' => $bulan,
                'mustahik_id' => $mustahikIds[$mIdx % $mCount],
                'program_id'  => $pid(2026, $prog),
            ]);
        }
    }
}
