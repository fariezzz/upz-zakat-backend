<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL with IF NOT EXISTS to make migration idempotent and robust
        $indexes = [
            // Muzakki indexes
            "CREATE INDEX IF NOT EXISTS muzakki_tipe_muzakki_index ON muzakki (tipe_muzakki)",
            "CREATE INDEX IF NOT EXISTS muzakki_kategori_index ON muzakki (kategori)",
            "CREATE INDEX IF NOT EXISTS muzakki_unit_kerja_index ON muzakki (unit_kerja)",
            "CREATE INDEX IF NOT EXISTS muzakki_created_at_index ON muzakki (created_at)",
            "CREATE INDEX IF NOT EXISTS muzakki_tipe_muzakki_kategori_index ON muzakki (tipe_muzakki, kategori)",

            // Transaksi indexes
            "CREATE INDEX IF NOT EXISTS transaksi_jenis_index ON transaksi (jenis)",
            "CREATE INDEX IF NOT EXISTS transaksi_tahun_index ON transaksi (tahun)",
            "CREATE INDEX IF NOT EXISTS transaksi_bulan_index ON transaksi (bulan)",
            "CREATE INDEX IF NOT EXISTS transaksi_kategori_index ON transaksi (kategori)",
            "CREATE INDEX IF NOT EXISTS transaksi_created_at_index ON transaksi (created_at)",
            "CREATE INDEX IF NOT EXISTS transaksi_jenis_tahun_index ON transaksi (jenis, tahun)",
            "CREATE INDEX IF NOT EXISTS transaksi_jenis_tahun_bulan_index ON transaksi (jenis, tahun, bulan)",
        ];

        foreach ($indexes as $sql) {
            DB::statement($sql);
        }
    }

    public function down(): void
    {
        // Drop indexes if they exist
        $indexes = [
            'muzakki_tipe_muzakki_index',
            'muzakki_kategori_index',
            'muzakki_unit_kerja_index',
            'muzakki_created_at_index',
            'muzakki_tipe_muzakki_kategori_index',
            'transaksi_jenis_index',
            'transaksi_tahun_index',
            'transaksi_bulan_index',
            'transaksi_kategori_index',
            'transaksi_created_at_index',
            'transaksi_jenis_tahun_index',
            'transaksi_jenis_tahun_bulan_index',
        ];

        foreach ($indexes as $index) {
            DB::statement("DROP INDEX IF EXISTS $index");
        }
    }
};
