<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            // tipe_muzakki: 'terdaftar' (registered) atau 'donor' (one-time donor)
            // Default 'terdaftar' untuk backward compatibility
            $table->enum('tipe_muzakki', ['terdaftar', 'donor'])->default('terdaftar')->after('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            $table->dropColumn('tipe_muzakki');
        });
    }
};
