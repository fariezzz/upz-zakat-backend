<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    use HasFactory;

    protected $table = 'jurnals';

    protected $fillable = [
        'tanggal',
        'kode_akun',
        'nama_akun',
        'keterangan',
        'debit',
        'kredit',
        'jenis',
        'referensi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'debit'   => 'integer',
        'kredit'  => 'integer',
    ];
}
