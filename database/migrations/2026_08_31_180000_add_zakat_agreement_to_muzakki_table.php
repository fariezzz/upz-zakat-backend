<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            if (!Schema::hasColumn('muzakki', 'jenis_zakat')) {
                $table->string('jenis_zakat', 100)->nullable()->after('kategori');
            }
            if (!Schema::hasColumn('muzakki', 'frekuensi')) {
                $table->string('frekuensi', 50)->nullable()->after('jenis_zakat');
            }
            if (!Schema::hasColumn('muzakki', 'nominal')) {
                $table->decimal('nominal', 15, 2)->nullable()->after('frekuensi');
            }
            if (!Schema::hasColumn('muzakki', 'metode_pembayaran')) {
                $table->string('metode_pembayaran', 100)->nullable()->after('nominal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            $table->dropColumn(['jenis_zakat', 'frekuensi', 'nominal', 'metode_pembayaran']);
        });
    }
};
