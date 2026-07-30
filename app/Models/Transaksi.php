<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'kode',
        'jenis',
        'kategori',
        'deskripsi',
        'nominal',
        'tahun',
        'bulan',
        'muzakki_id',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'tahun'   => 'integer',
        'bulan'   => 'integer',
    ];

    public function muzakki()
    {
        return $this->belongsTo(Muzakki::class);
    }
}
