<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // TRX-001, TRX-002, dst
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->string('kategori', 50); // Zakat Fitrah, Zakat Maal, Infaq, dll
            $table->string('deskripsi');
            $table->bigInteger('nominal'); // dalam rupiah
            $table->string('metode', 50)->nullable(); // Transfer Bank, Tunai, QRIS
            $table->integer('tahun');
            $table->tinyInteger('bulan'); // 1-12
            $table->foreignId('muzakki_id')->nullable()->constrained('muzakki')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
