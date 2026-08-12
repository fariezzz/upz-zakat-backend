<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mustahik extends Model
{
    protected $table = 'mustahik';

    protected $fillable = [
        'nama',
        'email',
        'no_hp',
        'alamat',
        'kategori',
        'status',
    ];
}
