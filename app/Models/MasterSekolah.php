<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSekolah extends Model
{
    use HasFactory;


    protected $fillable = [
        'nisn',
        'nama_sekolah',
        'alamat',
    ];
}
