<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enable pg_trgm extension if not already enabled
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        // Drop existing indexes if they exist (to avoid conflicts)
        $indexes = [
            'muzakki_nama_trgm_idx',
            'muzakki_nik_trgm_idx',
            'muzakki_nip_trgm_idx',
            'muzakki_email_trgm_idx',
            'muzakki_no_hp_trgm_idx',
            'muzakki_unit_kerja_trgm_idx',
            'muzakki_pekerjaan_trgm_idx',
            'muzakki_alamat_lengkap_trgm_idx',
            'muzakki_tempat_lahir_trgm_idx',
            'muzakki_kategori_trgm_idx',
        ];

        foreach ($indexes as $idx) {
            DB::statement("DROP INDEX IF EXISTS {$idx}");
        }

        // Create GIN indexes for trigram search on frequently searched columns
        DB::statement('CREATE INDEX muzakki_nama_trgm_idx ON muzakki USING gin (nama gin_trgm_ops)');
        DB::statement('CREATE INDEX muzakki_nik_trgm_idx ON muzakki USING gin (nik gin_trgm_ops)');
        DB::statement('CREATE INDEX muzakki_nip_trgm_idx ON muzakki USING gin (nip gin_trgm_ops)');
        DB::statement('CREATE INDEX muzakki_email_trgm_idx ON muzakki USING gin (email gin_trgm_ops)');
        DB::statement('CREATE INDEX muzakki_no_hp_trgm_idx ON muzakki USING gin (no_hp gin_trgm_ops)');
        DB::statement('CREATE INDEX muzakki_unit_kerja_trgm_idx ON muzakki USING gin (unit_kerja gin_trgm_ops)');
        DB::statement('CREATE INDEX muzakki_pekerjaan_trgm_idx ON muzakki USING gin (pekerjaan gin_trgm_ops)');
        DB::statement('CREATE INDEX muzakki_alamat_lengkap_trgm_idx ON muzakki USING gin (alamat_lengkap gin_trgm_ops)');
        DB::statement('CREATE INDEX muzakki_tempat_lahir_trgm_idx ON muzakki USING gin (tempat_lahir gin_trgm_ops)');
        DB::statement('CREATE INDEX muzakki_kategori_trgm_idx ON muzakki USING gin (kategori gin_trgm_ops)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS muzakki_nama_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS muzakki_nik_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS muzakki_nip_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS muzakki_email_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS muzakki_no_hp_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS muzakki_unit_kerja_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS muzakki_pekerjaan_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS muzakki_alamat_lengkap_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS muzakki_tempat_lahir_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS muzakki_kategori_trgm_idx');
    }
};