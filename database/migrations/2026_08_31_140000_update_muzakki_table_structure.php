<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            if (!Schema::hasColumn('muzakki', 'jenis_kelamin')) {
                $table->string('jenis_kelamin', 20)->nullable()->after('nip');
            }
            if (!Schema::hasColumn('muzakki', 'tempat_lahir')) {
                $table->string('tempat_lahir', 100)->nullable()->after('jenis_kelamin');
            }
            if (!Schema::hasColumn('muzakki', 'tanggal_lahir')) {
                $table->string('tanggal_lahir', 50)->nullable()->after('tempat_lahir');
            }
            if (!Schema::hasColumn('muzakki', 'pekerjaan')) {
                $table->string('pekerjaan', 100)->nullable()->after('tanggal_lahir');
            }
            if (!Schema::hasColumn('muzakki', 'alamat_lengkap')) {
                $table->text('alamat_lengkap')->nullable()->after('pekerjaan');
            }
            if (!Schema::hasColumn('muzakki', 'kategori')) {
                $table->string('kategori', 50)->nullable()->after('alamat_lengkap');
            }
            if (Schema::hasColumn('muzakki', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->dropColumn([
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'pekerjaan',
                'alamat_lengkap',
                'kategori',
            ]);
        });
    }
};
