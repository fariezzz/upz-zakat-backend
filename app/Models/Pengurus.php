<?php

namespace App\Models;

use App\Traits\CleansStorageFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    use HasFactory, CleansStorageFiles;

    protected $table = 'pengurus';

    protected $fillable = [
        'nama',
        'jabatan',
        'foto',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    /**
     * Trigger event Eloquent untuk membersihkan berkas asset fisik lokal
     */
    protected static function booted(): void
    {
        static::deleting(function (Pengurus $pengurus) {
            self::deleteStorageFile($pengurus->foto);
        });

        static::updating(function (Pengurus $pengurus) {
            if ($pengurus->isDirty('foto')) {
                self::deleteStorageFile($pengurus->getOriginal('foto'));
            }
        });
    }
}
