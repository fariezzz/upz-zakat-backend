<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            if (!Schema::hasColumn('muzakki', 'kesepakatan_zakat')) {
                $table->json('kesepakatan_zakat')->nullable()->after('kategori');
            }
            if (!Schema::hasColumn('muzakki', 'pilihan_bank')) {
                $table->string('pilihan_bank', 100)->nullable()->after('metode_pembayaran');
            }
            if (!Schema::hasColumn('muzakki', 'pilihan_ewallet')) {
                $table->string('pilihan_ewallet', 100)->nullable()->after('pilihan_bank');
            }
        });
    }

    public function down(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            $table->dropColumn(['kesepakatan_zakat', 'pilihan_bank', 'pilihan_ewallet']);
        });
    }
};
