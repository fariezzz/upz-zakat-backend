<?php

namespace Database\Seeders;

use App\Models\Mustahik;
use Illuminate\Database\Seeder;

class MustahikSeeder extends Seeder
{
    public function run(): void
    {
        $mustahikData = [
            ['nama' => 'Budi Santoso',        'alamat' => 'Jl. Cigoong No. 12, Tasikmalaya',     'kategori' => 'Fakir Miskin', 'no_hp' => '085211110001'],
            ['nama' => 'Siti Aminah',          'alamat' => 'Jl. Empangsari No. 5, Tasikmalaya',  'kategori' => 'Fakir Miskin', 'no_hp' => '085211110002'],
            ['nama' => "Ahmad Rifa'i",         'alamat' => 'Jl. Singaparna No. 33, Tasikmalaya', 'kategori' => 'Gharim',       'no_hp' => '085211110003'],
            ['nama' => 'Hj. Neneng Kurniasih', 'alamat' => 'Jl. Panglima No. 7, Tasikmalaya',   'kategori' => 'Fakir Miskin', 'no_hp' => '085211110004'],
            ['nama' => 'Dede Supriatna',       'alamat' => 'Jl. Rahayu No. 21, Tasikmalaya',    'kategori' => 'Ibnu Sabil',   'no_hp' => '085211110005'],
            ['nama' => 'Yayah Rokayah',        'alamat' => 'Jl. Nagarasari No. 14, Tasikmalaya','kategori' => 'Fakir Miskin', 'no_hp' => '085211110006'],
            ['nama' => 'Ujang Hermawan',       'alamat' => 'Jl. Gunung Gede No. 3, Tasikmalaya','kategori' => 'Gharim',       'no_hp' => '085211110007'],
            ['nama' => 'Imas Sukaesih',        'alamat' => 'Jl. Tamansari No. 9, Tasikmalaya',  'kategori' => 'Fakir Miskin', 'no_hp' => '085211110008'],
            ['nama' => 'Rahmat Hidayat',       'alamat' => 'Jl. Cihideung No. 17, Tasikmalaya', 'kategori' => 'Muallaf',      'no_hp' => '085211110009'],
            ['nama' => 'Wati Nuraeni',         'alamat' => 'Jl. Cipedes No. 25, Tasikmalaya',   'kategori' => 'Fakir Miskin', 'no_hp' => '085211110010'],
            ['nama' => 'Asep Ridwan',          'alamat' => 'Jl. Cilembang No. 8, Tasikmalaya',  'kategori' => 'Fakir Miskin', 'no_hp' => '085211110011'],
            ['nama' => 'Tini Sumarni',         'alamat' => 'Jl. Sindangraja No. 2, Tasikmalaya','kategori' => 'Gharim',       'no_hp' => '085211110012'],
            ['nama' => 'Cecep Nugraha',        'alamat' => 'Jl. Sukalaya No. 11, Tasikmalaya',  'kategori' => 'Fakir Miskin', 'no_hp' => '085211110013'],
            ['nama' => 'Euis Fatimah',         'alamat' => 'Jl. Sukaasih No. 6, Tasikmalaya',   'kategori' => 'Muallaf',      'no_hp' => '085211110014'],
            ['nama' => 'Tatang Sopandi',       'alamat' => 'Jl. Sukahurip No. 30, Tasikmalaya', 'kategori' => 'Ibnu Sabil',   'no_hp' => '085211110015'],
            ['nama' => 'Lilis Nurjanah',       'alamat' => 'Jl. Cikunir No. 19, Tasikmalaya',   'kategori' => 'Fakir Miskin', 'no_hp' => '085211110016'],
            ['nama' => 'Dani Ramdani',         'alamat' => 'Jl. Tawang No. 44, Tasikmalaya',    'kategori' => 'Gharim',       'no_hp' => '085211110017'],
            ['nama' => 'Rini Kartini',         'alamat' => 'Jl. Sumelap No. 1, Tasikmalaya',    'kategori' => 'Fakir Miskin', 'no_hp' => '085211110018'],
            ['nama' => 'Heri Gunawan',         'alamat' => 'Jl. Gobras No. 38, Tasikmalaya',    'kategori' => 'Fakir Miskin', 'no_hp' => '085211110019'],
            ['nama' => 'Sri Wahyuni',          'alamat' => 'Jl. Dadaha No. 15, Tasikmalaya',    'kategori' => 'Muallaf',      'no_hp' => '085211110020'],
        ];

        foreach ($mustahikData as $data) {
            Mustahik::create(array_merge($data, ['status' => 'aktif']));
        }
    }
}
