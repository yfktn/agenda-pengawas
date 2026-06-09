<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    protected $fillable = [
        'judul',
        'deskripsi',
        'deskripsi_hasil',
        'tanggal_mulai',
        'tanggal_berakhir',
        'created_by',
    ];
}
