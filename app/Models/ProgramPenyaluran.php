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
     * Hitung jumlah penerima unik (distinct mustahik_id) dari transaksi keluar.
     */
    public function getJumlahPenerimaAttribute(): int
    {
        if ($this->relationLoaded('transaksi')) {
            return $this->transaksi
                ->where('jenis', 'keluar')
                ->whereNotNull('mustahik_id')
                ->pluck('mustahik_id')
                ->unique()
                ->count();
        }
        return $this->transaksi()
            ->where('jenis', 'keluar')
            ->whereNotNull('mustahik_id')
            ->distinct('mustahik_id')
            ->count('mustahik_id');
    }

    /**
     * Hitung persentase progres donasi terkumpul (0-100)
     */
    public function getProgressAttribute(): int
    {
        if ($this->target_nominal <= 0) return 0;
        $terkumpul = $this->nominal_terkumpul;
        return (int) min(100, round(($terkumpul / $this->target_nominal) * 100));
    }

    /**
     * Total donasi yang terkumpul untuk program ini (transaksi masuk).
     */
    public function getNominalTerkumpulAttribute(): int
    {
        if ($this->relationLoaded('transaksi')) {
            return (int) $this->transaksi->where('jenis', 'masuk')->sum('nominal');
        }
        return (int) $this->transaksi()->where('jenis', 'masuk')->sum('nominal');
    }

    /**
     * Total nominal yang sudah disalurkan ke mustahik (transaksi keluar).
     */
    public function getNominalDisalurkanAttribute(): int
    {
        if ($this->relationLoaded('transaksi')) {
            return (int) $this->transaksi->where('jenis', 'keluar')->sum('nominal');
        }
        return (int) $this->transaksi()->where('jenis', 'keluar')->sum('nominal');
    }
}

