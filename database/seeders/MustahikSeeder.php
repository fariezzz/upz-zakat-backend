<?php

namespace Database\Seeders;

use App\Models\Mustahik;
use Illuminate\Database\Seeder;

class MustahikSeeder extends Seeder
{
    public function run(): void
    {
        $mustahikData = [
            ['nama' => 'Budi Santoso',         'nik' => '3278011503800001', 'alamat' => 'Jl. Cigoong No. 12, Tasikmalaya',     'kategori' => 'Fakir',       'no_hp' => '085211110001'],
            ['nama' => 'Siti Aminah',          'nik' => '3278014506850002', 'alamat' => 'Jl. Empangsari No. 5, Tasikmalaya',   'kategori' => 'Miskin',      'no_hp' => '085211110002'],
            ['nama' => "Ahmad Rifa'i",         'nik' => '3278021010780003', 'alamat' => 'Jl. Singaparna No. 33, Tasikmalaya',  'kategori' => 'Gharim',      'no_hp' => '085211110003'],
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
                ['nik' => $data['nik']],
                array_merge($data, ['status' => 'aktif'])
            );
        }
    }
}

