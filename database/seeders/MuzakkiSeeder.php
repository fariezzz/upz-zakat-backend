<?php

namespace Database\Seeders;

use App\Models\Muzakki;
use Illuminate\Database\Seeder;

class MuzakkiSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}

