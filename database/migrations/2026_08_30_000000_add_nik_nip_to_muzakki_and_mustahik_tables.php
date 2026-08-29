<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            $table->string('nik', 30)->nullable()->after('nama');
            $table->string('nip', 30)->nullable()->after('nik');
        });

        Schema::table('mustahik', function (Blueprint $table) {
            $table->string('nik', 30)->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            $table->dropColumn(['nik', 'nip']);
        });

        Schema::table('mustahik', function (Blueprint $table) {
            $table->dropColumn('nik');
        });
    }
};
