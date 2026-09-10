<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Muzakki;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@unsil.ac.id'],
            [
                'name'     => 'Administrator UPZ',
                'password' => Hash::make('password'),
                'role'     => 'administrator',
            ]
        );

        User::updateOrCreate(
            ['email' => 'bendahara@unsil.ac.id'],
            [
                'name'     => 'Bendahara UPZ Unsil',
                'password' => Hash::make('password'),
                'role'     => 'administrator',
            ]
        );

        User::updateOrCreate(
            ['email' => 'muzakki@unsil.ac.id'],
            [
                'name'           => 'Muzakki UPZ Unsil',
                'password'       => Hash::make('password'),
                'role'           => 'muzakki',
                'nip'            => '198501012010121001',
                'no_hp'          => '081234567890',
                'unit_kerja'     => 'Fakultas Teknik · Informatika',
                'is_first_login' => false,
            ]
        );

        Muzakki::updateOrCreate(
            ['email' => 'muzakki@unsil.ac.id'],
            [
                'nama'              => 'Muzakki UPZ Unsil',
                'nik'               => '3278010101850001',
                'nip'               => '198501012010121001',
                'jenis_kelamin'     => 'Laki-laki',
                'tempat_lahir'      => 'Tasikmalaya',
                'tanggal_lahir'     => '1985-01-01',
                'pekerjaan'         => 'Dosen',
                'alamat_lengkap'    => 'Jl. Siliwangi No. 24, Kec. Tawang, Kota Tasikmalaya',
                'email'             => 'muzakki@unsil.ac.id',
                'no_hp'             => '081234567890',
                'kategori'          => 'Dosen & Staf UNSIL',
                'unit_kerja'        => 'Fakultas Teknik · Informatika',
                'jenis_zakat'       => 'Zakat Penghasilan',
                'frekuensi'         => 'bulanan',
                'nominal'           => 250000,
                'metode_pembayaran' => 'Potong Gaji',
                'tipe_muzakki'      => 'terdaftar',
            ]
        );
    }
}

