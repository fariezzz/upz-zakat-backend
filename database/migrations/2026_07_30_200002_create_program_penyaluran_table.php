<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_penyaluran', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // PRG-001, dst
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->integer('jumlah_penerima')->default(0);
            $table->bigInteger('target_nominal'); // target dana program
            $table->bigInteger('nominal_disalurkan')->default(0);
            $table->enum('status', ['aktif', 'selesai', 'pending'])->default('aktif');
            $table->integer('tahun');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_penyaluran');
    }
};
