<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZakatAgreementRequest extends Model
{
    protected $table = 'zakat_agreement_requests';

    protected $fillable = [
        'muzakki_id',
        'nama_muzakki',
        'nip',
        'nik',
        'no_hp',
        'perubahan_diajukan',
        'kesepakatan_lama',
        'alasan',
        'status',
        'catatan_admin',
        'diproses_oleh',
        'diproses_at',
    ];

    protected $casts = [
        'perubahan_diajukan' => 'array',
        'kesepakatan_lama'   => 'array',
        'diproses_at'        => 'datetime',
    ];

    public function muzakki()
    {
        return $this->belongsTo(Muzakki::class, 'muzakki_id');
    }

    public function diprosesoleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
