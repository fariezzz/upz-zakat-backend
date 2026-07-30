<?php

namespace Database\Seeders;

use App\Models\Muzakki;
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
            ['email' => 'admin@upz-unsil.ac.id'],
            [
                'name'     => 'Admin UPZ',
                'password' => Hash::make('password'),
            ]
        );

        // ——————————————————————————————————
        // 2. Muzakki (Donors)
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
            $m = Muzakki::create(array_merge($data, ['status' => 'aktif']));
            $muzakkiIds[] = $m->id;
        }

        // ——————————————————————————————————
        // 3. Transaksi — tahun 2024 (tahun lalu, buat kalkulasi perubahan)
        // ——————————————————————————————————
        $trxCounter = 1;
        $tahun2024Masuk = [
            [1, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      22000000],
            [2, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      24000000],
            [3, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      22500000],
            [4, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      26000000],
            [5, 'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      27000000],
            [6, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',            7000000],
            [7, 'Infaq',       'Penerimaan Infaq - Civitas Akademika',            7500000],
            [8, 'Sedekah',     'Penerimaan Sedekah - Donasi Online',              3000000],
            [9, 'Sedekah',     'Penerimaan Sedekah - Kotak Amal',                 2500000],
            [10,'Infaq',       'Penerimaan Infaq - Kegiatan Ramadhan',             9000000],
            [11,'Dana Lainnya','Penerimaan Dana Sosial - Hibah',                   5000000],
            [12,'Zakat',       'Penerimaan Zakat Maal - Akhir Tahun',             28000000],
        ];

        foreach ($tahun2024Masuk as [$bulan, $kategori, $desc, $nominal]) {
            Transaksi::create([
                'kode'      => 'TRX-' . str_pad($trxCounter++, 3, '0', STR_PAD_LEFT),
                'jenis'     => 'masuk',
                'kategori'  => $kategori,
                'deskripsi' => $desc,
                'nominal'   => $nominal,
                'tahun'     => 2024,
                'bulan'     => $bulan,
                'muzakki_id'=> $muzakkiIds[array_rand($muzakkiIds)],
            ]);
        }

        $tahun2024Keluar = [
            [3,  'Penyaluran', 'Penyaluran - Program Beasiswa Mustahik Sem. 1',  25000000],
            [6,  'Penyaluran', 'Penyaluran - Santunan Yatim Semester 1',         15000000],
            [6,  'Penyaluran', 'Penyaluran - Bantuan Kesehatan',                  8000000],
            [9,  'Penyaluran', 'Penyaluran - Program Beasiswa Mustahik Sem. 2',  25000000],
            [12, 'Penyaluran', 'Penyaluran - Santunan Yatim Semester 2',         15000000],
            [12, 'Penyaluran', 'Penyaluran - Bantuan UMKM Mustahik',              5000000],
        ];

        foreach ($tahun2024Keluar as [$bulan, $kategori, $desc, $nominal]) {
            Transaksi::create([
                'kode'      => 'TRX-' . str_pad($trxCounter++, 3, '0', STR_PAD_LEFT),
                'jenis'     => 'keluar',
                'kategori'  => $kategori,
                'deskripsi' => $desc,
                'nominal'   => $nominal,
                'tahun'     => 2024,
                'bulan'     => $bulan,
            ]);
        }

        // ——————————————————————————————————
        // 4. Transaksi — tahun 2025 (tahun ini)
        // ——————————————————————————————————
        $tahun2025Masuk = [
            [1,  'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      25750000],
            [1,  'Infaq',       'Penerimaan Infaq - Civitas Akademika',            4250000],
            [2,  'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      27000000],
            [2,  'Infaq',       'Penerimaan Infaq - Civitas Akademika',            6000000],
            [2,  'Sedekah',     'Penerimaan Sedekah - Donasi Online',              3000000],
            [3,  'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      28000000],
            [3,  'Infaq',       'Penerimaan Infaq - Kegiatan Kampus',              9000000],
            [3,  'Sedekah',     'Penerimaan Sedekah - Ramadhan',                  12000000],
            [4,  'Zakat',       'Penerimaan Zakat Fitrah - Idul Fitri',           35000000],
            [4,  'Infaq',       'Penerimaan Infaq - Civitas Akademika',           18000000],
            [4,  'Sedekah',     'Penerimaan Sedekah - Donasi Online',              5000000],
            [5,  'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      25750000],
            [5,  'Infaq',       'Penerimaan Infaq - Civitas Akademika',            8250000],
            [5,  'Sedekah',     'Penerimaan Sedekah - Donasi Online',               750000],
            [6,  'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      22000000],
            [6,  'Infaq',       'Penerimaan Infaq - Civitas Akademika',            5000000],
            [6,  'Dana Lainnya','Penerimaan Dana Sosial - CSR',                   10000000],
            [7,  'Zakat',       'Penerimaan Zakat - Payroll Dosen & Tendik',      30000000],
            [7,  'Infaq',       'Penerimaan Infaq - Civitas Akademika',            9000000],
            [7,  'Sedekah',     'Penerimaan Sedekah - Donasi Online',              4000000],
        ];

        foreach ($tahun2025Masuk as [$bulan, $kategori, $desc, $nominal]) {
            Transaksi::create([
                'kode'      => 'TRX-' . str_pad($trxCounter++, 3, '0', STR_PAD_LEFT),
                'jenis'     => 'masuk',
                'kategori'  => $kategori,
                'deskripsi' => $desc,
                'nominal'   => $nominal,
                'tahun'     => 2025,
                'bulan'     => $bulan,
                'muzakki_id'=> $muzakkiIds[array_rand($muzakkiIds)],
            ]);
        }

        $tahun2025Keluar = [
            [3,  'Penyaluran', 'Penyaluran - Program Beasiswa Mustahik Sem. 1',  35000000],
            [4,  'Penyaluran', 'Penyaluran - Santunan Yatim & Dhuafa',           12500000],
            [4,  'Penyaluran', 'Penyaluran - Bantuan Lebaran Mustahik',           15000000],
            [5,  'Penyaluran', 'Penyaluran - Bantuan Kesehatan Mustahik',          8500000],
            [6,  'Penyaluran', 'Penyaluran - Bantuan UMKM Mustahik',              10000000],
            [7,  'Penyaluran', 'Penyaluran - Santunan Yatim Sem. 2',             12500000],
        ];

        foreach ($tahun2025Keluar as [$bulan, $kategori, $desc, $nominal]) {
            Transaksi::create([
                'kode'      => 'TRX-' . str_pad($trxCounter++, 3, '0', STR_PAD_LEFT),
                'jenis'     => 'keluar',
                'kategori'  => $kategori,
                'deskripsi' => $desc,
                'nominal'   => $nominal,
                'tahun'     => 2025,
                'bulan'     => $bulan,
            ]);
        }

        // ——————————————————————————————————
        // 5. Program Penyaluran Aktif
        // ——————————————————————————————————
        $programs = [
            [
                'kode'               => 'PRG-001',
                'nama'               => 'Beasiswa Mahasiswa Mustahik',
                'deskripsi'          => 'Program beasiswa untuk mahasiswa Unsil yang termasuk kategori mustahik (penerima zakat) dengan keterbatasan ekonomi.',
                'jumlah_penerima'    => 120,
                'target_nominal'     => 350000000,
                'nominal_disalurkan' => 245000000,
                'status'             => 'aktif',
                'tahun'              => 2025,
            ],
            [
                'kode'               => 'PRG-002',
                'nama'               => 'Santunan Yatim & Dhuafa',
                'deskripsi'          => 'Santunan rutin bulanan untuk anak yatim dan kaum dhuafa di lingkungan sekitar kampus Unsil.',
                'jumlah_penerima'    => 210,
                'target_nominal'     => 300000000,
                'nominal_disalurkan' => 180000000,
                'status'             => 'aktif',
                'tahun'              => 2025,
            ],
            [
                'kode'               => 'PRG-003',
                'nama'               => 'Bantuan Kesehatan',
                'deskripsi'          => 'Bantuan biaya kesehatan dan pengobatan bagi mustahik yang membutuhkan layanan medis.',
                'jumlah_penerima'    => 85,
                'target_nominal'     => 190000000,
                'nominal_disalurkan' => 95000000,
                'status'             => 'aktif',
                'tahun'              => 2025,
            ],
            [
                'kode'               => 'PRG-004',
                'nama'               => 'Bantuan UMKM Mustahik',
                'deskripsi'          => 'Modal usaha dan pendampingan untuk mustahik yang ingin berwirausaha agar mandiri secara ekonomi.',
                'jumlah_penerima'    => 40,
                'target_nominal'     => 165000000,
                'nominal_disalurkan' => 75000000,
                'status'             => 'aktif',
                'tahun'              => 2025,
            ],
        ];

        foreach ($programs as $prog) {
            ProgramPenyaluran::create($prog);
        }
    }
}
