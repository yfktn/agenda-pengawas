<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pertanyaan extends Model
{
    protected $fillable = [
        'user_id',
        'master_sekolah_id',
        'judul',
        'isi',
    ];
}
