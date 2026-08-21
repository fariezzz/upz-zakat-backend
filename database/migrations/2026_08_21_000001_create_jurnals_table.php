<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kode_akun', 20)->nullable();
            $table->string('nama_akun', 100);
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('kredit')->default(0);
            // 'masuk' = pendapatan/penerimaan, 'keluar' = pengeluaran/beban
            $table->enum('jenis', ['masuk', 'keluar'])->default('masuk');
            $table->string('referensi', 50)->nullable(); // misal kode transaksi terkait
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};
