<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kategori')->default('Kegiatan'); // Kegiatan, Penyaluran, Pemberdayaan, Pengumuman, dll.
            $table->text('ringkasan')->nullable();
            $table->longText('konten'); // HTML dari TipTap
            $table->string('gambar')->nullable(); // URL atau path upload gambar
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->dateTime('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};
