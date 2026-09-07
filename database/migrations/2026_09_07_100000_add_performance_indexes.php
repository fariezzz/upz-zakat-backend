<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Helper function to check if index exists
        $indexExists = function($table, $indexName) {
            $result = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $indexName]);
            return count($result) > 0;
        };

        Schema::table('muzakki', function (Blueprint $table) use ($indexExists) {
            // Indexes untuk kolom yang sering di-filter (skip jika sudah ada)
            if (!$indexExists('muzakki', 'muzakki_tipe_muzakki_index')) {
                $table->index('tipe_muzakki');
            }
            if (!$indexExists('muzakki', 'muzakki_kategori_index')) {
                $table->index('kategori');
            }
            if (!$indexExists('muzakki', 'muzakki_unit_kerja_index')) {
                $table->index('unit_kerja');
            }
            if (!$indexExists('muzakki', 'muzakki_created_at_index')) {
                $table->index('created_at');
            }
            // Composite index untuk kombinasi filter yang sering digunakan
            if (!$indexExists('muzakki', 'muzakki_tipe_muzakki_kategori_index')) {
                $table->index(['tipe_muzakki', 'kategori']);
            }
        });

        Schema::table('transaksi', function (Blueprint $table) use ($indexExists) {
            // Indexes untuk kolom yang sering di-filter (skip jika sudah ada)
            if (!$indexExists('transaksi', 'transaksi_jenis_index')) {
                $table->index('jenis');
            }
            if (!$indexExists('transaksi', 'transaksi_tahun_index')) {
                $table->index('tahun');
            }
            if (!$indexExists('transaksi', 'transaksi_bulan_index')) {
                $table->index('bulan');
            }
            if (!$indexExists('transaksi', 'transaksi_kategori_index')) {
                $table->index('kategori');
            }
            if (!$indexExists('transaksi', 'transaksi_created_at_index')) {
                $table->index('created_at');
            }
            // Composite indexes untuk kombinasi filter yang sering digunakan
            if (!$indexExists('transaksi', 'transaksi_jenis_tahun_index')) {
                $table->index(['jenis', 'tahun']);
            }
            if (!$indexExists('transaksi', 'transaksi_jenis_tahun_bulan_index')) {
                $table->index(['jenis', 'tahun', 'bulan']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            $table->dropIndex(['tipe_muzakki']);
            $table->dropIndex(['kategori']);
            $table->dropIndex(['unit_kerja']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['tipe_muzakki', 'kategori']);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex(['jenis']);
            $table->dropIndex(['tahun']);
            $table->dropIndex(['bulan']);
            $table->dropIndex(['kategori']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['jenis', 'tahun']);
            $table->dropIndex(['jenis', 'tahun', 'bulan']);
        });
    }
};
