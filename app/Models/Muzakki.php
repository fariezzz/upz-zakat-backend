<?php

namespace App\Models;

use App\Traits\ClearsDashboardCache;
use Illuminate\Database\Eloquent\Model;

class Muzakki extends Model
{
    use ClearsDashboardCache;

    protected $table = 'muzakki';

    protected $fillable = [
        'nama',
        'nik',
        'nip',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'pekerjaan',
        'alamat_lengkap',
        'email',
        'no_hp',
        'kategori',
        'unit_kerja',
        'jenis_zakat',
        'frekuensi',
        'nominal',
        'metode_pembayaran',
        'kesepakatan_zakat',
        'pilihan_bank',
        'pilihan_ewallet',
        'tipe_muzakki',
    ];

    protected $casts = [
        'kesepakatan_zakat' => 'array',
        'nominal'           => 'float',
    ];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }

    /**
     * Scope query untuk kategori Dosen & Staf UNSIL
     */
    public function scopeDosenStaf($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->where('kategori', 'ilike', '%Dosen%')
                    ->orWhere('kategori', 'ilike', '%Staf%')
                    ->orWhere('kategori', 'ilike', '%Civitas%')
                    ->orWhere('kategori', 'ilike', '%UNSIL%');
            })->orWhere(function ($sub) {
                $sub->whereNull('kategori')
                    ->where(function ($sub2) {
                        $sub2->whereNotNull('nip')
                             ->orWhere(function ($sub3) {
                                 $sub3->whereNotNull('unit_kerja')
                                      ->where('unit_kerja', '!=', '')
                                      ->where('unit_kerja', '!=', 'Masyarakat Umum')
                                      ->where('unit_kerja', '!=', 'Umum');
                             });
                    });
            });
        });
    }

    /**
     * Scope query untuk kategori Masyarakat Umum
     */
    public function scopeUmum($query)
    {
        return $query->where(function ($q) {
            $q->where('kategori', 'ilike', '%Umum%')
              ->orWhere(function ($sub) {
                  $sub->whereNull('kategori')
                      ->whereNull('nip')
                      ->where(function ($sub2) {
                          $sub2->whereNull('unit_kerja')
                               ->orWhere('unit_kerja', '')
                               ->orWhere('unit_kerja', 'Masyarakat Umum')
                               ->orWhere('unit_kerja', 'Umum');
                      });
              });
        });
    }
}
