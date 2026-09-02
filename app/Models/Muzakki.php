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
}
