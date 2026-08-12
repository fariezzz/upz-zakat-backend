<?php

namespace App\Models;

use App\Traits\ClearsDashboardCache;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use ClearsDashboardCache;

    protected $table = 'transaksi';

    protected $fillable = [
        'kode',
        'jenis',
        'kategori',
        'deskripsi',
        'nominal',
        'metode',
        'tahun',
        'bulan',
        'muzakki_id',
        'mustahik_id',
        'program_id',
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

    public function mustahik()
    {
        return $this->belongsTo(Mustahik::class);
    }

    public function program()
    {
        return $this->belongsTo(ProgramPenyaluran::class, 'program_id');
    }
}
