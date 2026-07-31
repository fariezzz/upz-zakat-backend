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
        'email',
        'no_hp',
        'unit_kerja',
        'status',
    ];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
