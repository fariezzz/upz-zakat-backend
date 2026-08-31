<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zakat_agreement_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muzakki_id')->constrained('muzakki')->cascadeOnDelete();
            $table->string('nama_muzakki', 150);
            $table->string('nip', 30)->nullable();
            $table->string('nik', 30)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->json('perubahan_diajukan'); // array kesepakatan_zakat baru yang diminta
            $table->json('kesepakatan_lama')->nullable(); // snapshot kesepakatan sebelumnya
            $table->text('alasan')->nullable();
            // status: pending | disetujui | ditolak
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zakat_agreement_requests');
    }
};
