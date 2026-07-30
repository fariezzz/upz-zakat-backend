<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramPenyaluran extends Model
{
    protected $table = 'program_penyaluran';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'jumlah_penerima',
        'target_nominal',
        'nominal_disalurkan',
        'status',
        'tahun',
    ];

    protected $casts = [
        'jumlah_penerima'    => 'integer',
        'target_nominal'     => 'integer',
        'nominal_disalurkan' => 'integer',
        'tahun'              => 'integer',
    ];

    /**
     * Hitung persentase progres penyaluran (0-100)
     */
    public function getProgressAttribute(): int
    {
        if ($this->target_nominal <= 0) return 0;
        return (int) min(100, round(($this->nominal_disalurkan / $this->target_nominal) * 100));
    }
}
