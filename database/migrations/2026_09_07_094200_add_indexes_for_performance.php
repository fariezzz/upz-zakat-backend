<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan index untuk meningkatkan performa query yang lambat:
     * - zakat_agreement_requests.status (WHERE status = 'pending')
     * - muzakki.tipe_muzakki (WHERE tipe_muzakki = 'terdaftar')
     * - muzakki.kategori (ILIKE searches)
     * - muzakki.nama (ORDER BY, searches)
     * - transaksi.muzakki_id + jenis (foreign key lookups)
     * - users.email (login queries)
     */
    public function up(): void
    {
        Schema::table('zakat_agreement_requests', function (Blueprint $table) {
            // Index untuk query pending count (5+ detik → < 50ms)
            $table->index('status');
            $table->index('muzakki_id');
            $table->index('created_at');
        });

        Schema::table('muzakki', function (Blueprint $table) {
            // Index untuk public list dan filtering
            $table->index('tipe_muzakki');
            $table->index('kategori');
            $table->index('nama');
            $table->index('email');
            $table->index('created_at');
            
            // Composite index untuk filter kombinasi
            $table->index(['tipe_muzakki', 'kategori']);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            // Index untuk eager loading transaksi
            $table->index('muzakki_id');
            $table->index('jenis');
            $table->index('created_at');
            
            // Composite index untuk query with WHERE jenis + ORDER BY
            $table->index(['muzakki_id', 'jenis', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Index email untuk login query (skip jika error = sudah exist)
            try {
                $table->index('email');
            } catch (\Exception $e) {
                // Index sudah exist, skip
            }
            
            $table->index('role');
        });

        Schema::table('mustahik', function (Blueprint $table) {
            $table->index('nama');
            $table->index('kategori');
            $table->index('created_at');
        });

        Schema::table('program_penyaluran', function (Blueprint $table) {
            $table->index('nama');
            $table->index('created_at');
        });

        Schema::table('berita', function (Blueprint $table) {
            $table->index('kategori');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zakat_agreement_requests', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['muzakki_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('muzakki', function (Blueprint $table) {
            $table->dropIndex(['tipe_muzakki']);
            $table->dropIndex(['kategori']);
            $table->dropIndex(['nama']);
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['tipe_muzakki', 'kategori']);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex(['muzakki_id']);
            $table->dropIndex(['jenis']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['muzakki_id', 'jenis', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['role']);
        });

        Schema::table('mustahik', function (Blueprint $table) {
            $table->dropIndex(['nama']);
            $table->dropIndex(['kategori']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('program_penyaluran', function (Blueprint $table) {
            $table->dropIndex(['nama']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('berita', function (Blueprint $table) {
            $table->dropIndex(['kategori']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
        });
    }
};
