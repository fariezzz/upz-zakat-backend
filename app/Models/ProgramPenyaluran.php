<?php

namespace App\Models;

use App\Traits\ClearsDashboardCache;
use Illuminate\Database\Eloquent\Model;

class ProgramPenyaluran extends Model
{
    use ClearsDashboardCache;

    protected $table = 'program_penyaluran';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'target_nominal',
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
     * Relasi: semua transaksi penyaluran yang terhubung ke program ini.
     */
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'program_id');
    }

    /**
     * Hitung jumlah penerima unik (distinct mustahik_id) dari transaksi.
     * Dengan withCount('transaksi') atau loadCount, atau lewat accessor ini.
     */
    public function getJumlahPenerimaAttribute(): int
    {
        if ($this->relationLoaded('transaksi')) {
            return $this->transaksi
                ->whereNotNull('mustahik_id')
                ->pluck('mustahik_id')
                ->unique()
                ->count();
        }
        return $this->transaksi()
            ->whereNotNull('mustahik_id')
            ->distinct('mustahik_id')
            ->count('mustahik_id');
    }

    /**
     * Hitung persentase progres penyaluran (0-100)
     */
    public function getProgressAttribute(): int
    {
        if ($this->target_nominal <= 0) return 0;
        $disalurkan = $this->relationLoaded('transaksi')
            ? $this->transaksi->sum('nominal')
            : $this->transaksi()->sum('nominal');
        return (int) min(100, round(($disalurkan / $this->target_nominal) * 100));
    }

    /**
     * Total nominal yang sudah disalurkan (computed dari transaksi).
     */
    public function getNominalDisalurkanAttribute(): int
    {
        if ($this->relationLoaded('transaksi')) {
            return (int) $this->transaksi->sum('nominal');
        }
        return (int) $this->transaksi()->sum('nominal');
    }
}
