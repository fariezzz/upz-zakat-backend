<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data transaksi dan data terkait sebelum seed ulang
        DB::table('transaksi')->delete();

        $this->call([
            UserSeeder::class,
            MuzakkiSeeder::class,
            MustahikSeeder::class,
            ProgramSeeder::class,
            TransaksiSeeder::class,
            BeritaSeeder::class,
        ]);
    }
}

