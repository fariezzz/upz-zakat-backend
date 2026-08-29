<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mustahik extends Model
{
    protected $table = 'mustahik';

    protected $fillable = [
        'nama',
        'nik',
        'email',
        'no_hp',
        'alamat',
        'kategori',
        'status',
    ];
}
