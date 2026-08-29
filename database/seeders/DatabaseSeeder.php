<?php

namespace Database\Seeders;

use App\Models\Muzakki;
use App\Models\Mustahik;
use App\Models\ProgramPenyaluran;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
            ['name' => 'Admin UPZ', 'password' => Hash::make('password'), 'role' => 'administrator']
        );

        // Hapus semua data transaksi terlebih dahulu sebelum seed ulang
        DB::table('transaksi')->delete();

        // ——————————————————————————————————
        // 2. Muzakki (Dosen/Staf Unsil & Umum)
        // ——————————————————————————————————
        $muzakkiData = [
            // Dosen / Staf — format: "Nama Fakultas · Nama Jurusan"
            ['nama' => 'Dr. Ahmad Fauzi, M.Pd.',       'nip' => '197503122001121001', 'nik' => null, 'email' => 'ahmadpauzi@unsil.ac.id',  'no_hp' => '08111000001', 'unit_kerja' => 'Fakultas Keguruan dan Ilmu Pendidikan (FKIP) · Pendidikan Matematika'],
            ['nama' => 'Prof. Siti Rahayu, Ph.D.',     'nip' => '196808251994032002', 'nik' => null, 'email' => 'sitirahayu@unsil.ac.id',  'no_hp' => '08111000002', 'unit_kerja' => 'Fakultas Ekonomi dan Bisnis · Manajemen'],
            ['nama' => 'Drs. Hendra Kusuma, M.Si.',    'nip' => '197005101998021003', 'nik' => null, 'email' => 'hendrak@unsil.ac.id',     'no_hp' => '08111000003', 'unit_kerja' => 'Fakultas Ilmu Sosial dan Ilmu Politik (FISIP) · Hukum Bisnis'],
            ['nama' => 'Ir. Budi Santoso, M.T.',       'nip' => '197211152000121004', 'nik' => null, 'email' => 'budisantoso@unsil.ac.id', 'no_hp' => '08111000004', 'unit_kerja' => 'Fakultas Teknik · Teknik Sipil'],
            ['nama' => 'Dr. Dewi Lestari, M.Kes.',     'nip' => '198104202008012005', 'nik' => null, 'email' => 'dewilestari@unsil.ac.id', 'no_hp' => '08111000005', 'unit_kerja' => 'Fakultas Ilmu Kesehatan · Kesehatan Masyarakat'],
            ['nama' => 'Dra. Ratna Wijaya, M.Pd.',     'nip' => '197309181999032006', 'nik' => null, 'email' => 'ratnaw@unsil.ac.id',      'no_hp' => '08111000006', 'unit_kerja' => 'Fakultas Keguruan dan Ilmu Pendidikan (FKIP) · Pendidikan Bahasa Indonesia'],
            ['nama' => 'H. Supriadi, S.E., M.M.',      'nip' => '197602052003121007', 'nik' => null, 'email' => 'supriadi@unsil.ac.id',    'no_hp' => '08111000007', 'unit_kerja' => 'Fakultas Ekonomi dan Bisnis · Akuntansi'],
            ['nama' => 'Dr. Rina Marlina, M.Si.',      'nip' => '198207142009122008', 'nik' => null, 'email' => 'rinamarlin@unsil.ac.id',  'no_hp' => '08111000008', 'unit_kerja' => 'Fakultas Pertanian · Agroteknologi'],
            ['nama' => 'Dr. Indra Permana, M.Kom.',    'nip' => '198501302012121009', 'nik' => null, 'email' => 'indrapermana@unsil.ac.id','no_hp' => '08111000009', 'unit_kerja' => 'Fakultas Teknik · Informatika'],
            ['nama' => 'Hj. Sari Kusumadewi, S.Pd.',   'nip' => '198012112006042010', 'nik' => null, 'email' => 'sarikusu@unsil.ac.id',   'no_hp' => '08111000010', 'unit_kerja' => 'Fakultas Keguruan dan Ilmu Pendidikan (FKIP) · Pendidikan Biologi'],
            ['nama' => 'Prof. Bambang Wijaya, Ph.D.',  'nip' => '196504031990031011', 'nik' => null, 'email' => 'bambangw@unsil.ac.id',    'no_hp' => '08111000011', 'unit_kerja' => 'Fakultas Ekonomi dan Bisnis · Ekonomi Pembangunan'],
            ['nama' => 'Dra. Nani Suryani, M.Pd.',     'nip' => '197406222000032012', 'nik' => null, 'email' => 'nanisury@unsil.ac.id',    'no_hp' => '08111000012', 'unit_kerja' => 'Fakultas Keguruan dan Ilmu Pendidikan (FKIP) · Pendidikan Sejarah'],
            ['nama' => 'Dr. Fajar Nugraha, M.T.',      'nip' => '198310082010121013', 'nik' => null, 'email' => 'fajarnug@unsil.ac.id',    'no_hp' => '08111000013', 'unit_kerja' => 'Fakultas Teknik · Teknik Elektro'],
            ['nama' => 'Ir. Yuli Ratnasari, M.P.',     'nip' => '197805172005012014', 'nik' => null, 'email' => 'yuliratn@unsil.ac.id',    'no_hp' => '08111000014', 'unit_kerja' => 'Fakultas Pertanian · Agribisnis'],
            ['nama' => 'Dr. Asep Hermawan, M.Ag.',     'nip' => '197711292003121015', 'nik' => null, 'email' => 'asepherm@unsil.ac.id',    'no_hp' => '08111000015', 'unit_kerja' => 'Fakultas Agama Islam · Ekonomi Syariah'],
            ['nama' => 'Drs. Cecep Saepulloh, M.Si.',  'nip' => '197103141997021016', 'nik' => null, 'email' => 'cecepsaep@unsil.ac.id',   'no_hp' => '08111000016', 'unit_kerja' => 'Fakultas Ilmu Sosial dan Ilmu Politik (FISIP) · Ilmu Politik'],
            ['nama' => 'Dr. Fitri Handayani, M.Gz.',   'nip' => '198609022014022017', 'nik' => null, 'email' => 'fitrih@unsil.ac.id',      'no_hp' => '08111000017', 'unit_kerja' => 'Fakultas Ilmu Kesehatan · Gizi'],
            ['nama' => 'M. Rizki Fauzan, S.Kom., M.T.','nip' => '198912042018011018', 'nik' => null, 'email' => 'rizkif@unsil.ac.id',      'no_hp' => '08111000018', 'unit_kerja' => 'Fakultas Teknik · Sistem Informasi'],
            ['nama' => 'Dr. Lina Nurhayati, M.Pd.',    'nip' => '197901252005012019', 'nik' => null, 'email' => 'linanurh@unsil.ac.id',    'no_hp' => '08111000019', 'unit_kerja' => 'Fakultas Keguruan dan Ilmu Pendidikan (FKIP) · Pendidikan Bahasa Inggris'],
            ['nama' => 'Drs. Wahyu Setiawan, M.M.',    'nip' => '197208191998031020', 'nik' => null, 'email' => 'wahyuset@unsil.ac.id',    'no_hp' => '08111000020', 'unit_kerja' => 'Fakultas Ekonomi dan Bisnis · Perbankan dan Keuangan Digital (D4)'],
            // Masyarakat Umum
            ['nama' => 'Rd. Andrian Saputra',           'nip' => null, 'nik' => '3278011204850001', 'email' => null,                      'no_hp' => '08222000001', 'unit_kerja' => 'Masyarakat Umum'],
            ['nama' => 'Fitriani Dewi',                 'nip' => null, 'nik' => '3278025508900002', 'email' => 'fitridewi@gmail.com',     'no_hp' => '08222000002', 'unit_kerja' => 'Masyarakat Umum'],
            ['nama' => 'H. Maman Abdurrahman',          'nip' => null, 'nik' => '3278010107720003', 'email' => null,                      'no_hp' => '08222000003', 'unit_kerja' => 'Masyarakat Umum'],
            ['nama' => 'Yeni Susanti',                  'nip' => null, 'nik' => '3278036011920004', 'email' => 'yenisus@gmail.com',       'no_hp' => '08222000004', 'unit_kerja' => 'Masyarakat Umum'],
            ['nama' => 'Dani Firmansyah',               'nip' => null, 'nik' => '3278011909880005', 'email' => null,                      'no_hp' => '08222000005', 'unit_kerja' => 'Masyarakat Umum'],
        ];

        foreach ($muzakkiData as $data) {
            Muzakki::updateOrCreate(
                ['nama' => $data['nama']],
                array_merge($data, ['status' => 'aktif'])
            );
        }

        // ——————————————————————————————————
        // 3. Mustahik
        // ——————————————————————————————————
        $mustahikData = [
            ['nama' => 'Budi Santoso',         'nik' => '3278011503800001', 'alamat' => 'Jl. Cigoong No. 12, Tasikmalaya',     'kategori' => 'Fakir',       'no_hp' => '085211110001'],
            ['nama' => 'Siti Aminah',          'nik' => '3278014506850002', 'alamat' => 'Jl. Empangsari No. 5, Tasikmalaya',   'kategori' => 'Miskin',      'no_hp' => '085211110002'],
            ['nama' => 'Ahmad Rifa\'i',        'nik' => '3278021010780003', 'alamat' => 'Jl. Singaparna No. 33, Tasikmalaya',  'kategori' => 'Gharim',      'no_hp' => '085211110003'],
            ['nama' => 'Hj. Neneng Kurniasih', 'nik' => '3278015201650004', 'alamat' => 'Jl. Panglima No. 7, Tasikmalaya',    'kategori' => 'Miskin',      'no_hp' => '085211110004'],
            ['nama' => 'Dede Supriatna',       'nik' => '3278032204920005', 'alamat' => 'Jl. Rahayu No. 21, Tasikmalaya',     'kategori' => 'Ibnu Sabil',  'no_hp' => '085211110005'],
            ['nama' => 'Yayah Rokayah',        'nik' => '3278016008700006', 'alamat' => 'Jl. Nagarasari No. 14, Tasikmalaya', 'kategori' => 'Fakir',       'no_hp' => '085211110006'],
            ['nama' => 'Ujang Hermawan',       'nik' => '3278021405830007', 'alamat' => 'Jl. Gunung Gede No. 3, Tasikmalaya', 'kategori' => 'Gharim',      'no_hp' => '085211110007'],
            ['nama' => 'Imas Sukaesih',        'nik' => '3278015012880008', 'alamat' => 'Jl. Tamansari No. 9, Tasikmalaya',   'kategori' => 'Miskin',      'no_hp' => '085211110008'],
            ['nama' => 'Rahmat Hidayat',       'nik' => '3278030509950009', 'alamat' => 'Jl. Cihideung No. 17, Tasikmalaya',  'kategori' => 'Muallaf',     'no_hp' => '085211110009'],
            ['nama' => 'Wati Nuraeni',         'nik' => '3278014802910010', 'alamat' => 'Jl. Cipedes No. 25, Tasikmalaya',    'kategori' => 'Miskin',      'no_hp' => '085211110010'],
            ['nama' => 'Asep Ridwan',          'nik' => '3278020307840011', 'alamat' => 'Jl. Cilembang No. 8, Tasikmalaya',   'kategori' => 'Fakir',       'no_hp' => '085211110011'],
            ['nama' => 'Tini Sumarni',         'nik' => '3278016511760012', 'alamat' => 'Jl. Sindangraja No. 2, Tasikmalaya', 'kategori' => 'Gharim',      'no_hp' => '085211110012'],
            ['nama' => 'Cecep Nugraha',        'nik' => '3278031804890013', 'alamat' => 'Jl. Sukalaya No. 11, Tasikmalaya',   'kategori' => 'Miskin',      'no_hp' => '085211110013'],
            ['nama' => 'Euis Fatimah',         'nik' => '3278015509930014', 'alamat' => 'Jl. Sukaasih No. 6, Tasikmalaya',    'kategori' => 'Muallaf',     'no_hp' => '085211110014'],
            ['nama' => 'Tatang Sopandi',       'nik' => '3278021101820015', 'alamat' => 'Jl. Sukahurip No. 30, Tasikmalaya',  'kategori' => 'Ibnu Sabil',  'no_hp' => '085211110015'],
            ['nama' => 'Lilis Nurjanah',       'nik' => '3278014208750016', 'alamat' => 'Jl. Cikunir No. 19, Tasikmalaya',    'kategori' => 'Fakir',       'no_hp' => '085211110016'],
            ['nama' => 'Dani Ramdani',         'nik' => '3278032703870017', 'alamat' => 'Jl. Tawang No. 44, Tasikmalaya',     'kategori' => 'Gharim',      'no_hp' => '085211110017'],
            ['nama' => 'Rini Kartini',         'nik' => '3278015912900018', 'alamat' => 'Jl. Sumelap No. 1, Tasikmalaya',     'kategori' => 'Miskin',      'no_hp' => '085211110018'],
            ['nama' => 'Heri Gunawan',         'nik' => '3278020806810019', 'alamat' => 'Jl. Gobras No. 38, Tasikmalaya',     'kategori' => 'Fakir',       'no_hp' => '085211110019'],
            ['nama' => 'Sri Wahyuni',          'nik' => '3278014107960020', 'alamat' => 'Jl. Dadaha No. 15, Tasikmalaya',     'kategori' => 'Muallaf',     'no_hp' => '085211110020'],
            ['nama' => 'Kosasih Suparman',     'nik' => '3278031205790021', 'alamat' => 'Jl. Argasari No. 7, Tasikmalaya',    'kategori' => 'Fi Sabilillah','no_hp' => '085211110021'],
            ['nama' => 'Neni Rohaeni',         'nik' => '3278016410730022', 'alamat' => 'Jl. Cibeureum No. 22, Tasikmalaya',  'kategori' => 'Fakir',       'no_hp' => '085211110022'],
            ['nama' => 'Ade Ruhiat',           'nik' => '3278022904860023', 'alamat' => 'Jl. Ciawi No. 5, Tasikmalaya',       'kategori' => 'Miskin',      'no_hp' => '085211110023'],
            ['nama' => 'Yayat Sudrajat',       'nik' => '3278011608840024', 'alamat' => 'Jl. Mangkubumi No. 18, Tasikmalaya', 'kategori' => 'Gharim',      'no_hp' => '085211110024'],
            ['nama' => 'Cucu Suminar',         'nik' => '3278035803910025', 'alamat' => 'Jl. Bantarsari No. 3, Tasikmalaya',  'kategori' => 'Miskin',      'no_hp' => '085211110025'],
        ];

        foreach ($mustahikData as $data) {
            Mustahik::updateOrCreate(
                ['nama' => $data['nama']],
                array_merge($data, ['status' => 'aktif'])
            );
        }

        // ——————————————————————————————————
        // 4. Program Penyaluran
        // ——————————————————————————————————
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
                ProgramPenyaluran::firstOrCreate(
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
