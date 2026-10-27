<?php

namespace App\Models;

use App\Traits\CleansStorageFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    use HasFactory, CleansStorageFiles;

    protected $table = 'berita';

    protected $fillable = [
        'judul',
        'slug',
        'kategori',
        'ringkasan',
        'konten',
        'gambar',
        'status',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Trigger event Eloquent untuk membersihkan berkas asset fisik lokal
     */
    protected static function booted(): void
    {
        static::deleting(function (Berita $berita) {
            self::deleteStorageFile($berita->gambar);
        });

        static::updating(function (Berita $berita) {
            if ($berita->isDirty('gambar')) {
                self::deleteStorageFile($berita->getOriginal('gambar'));
            }
        });
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
