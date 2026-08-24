<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    use HasFactory;

    protected $table = 'cabang';

    protected $fillable = [
        'kode_cabang',
        'nama_cabang',
        'alamat',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function barangStok()
    {
        return $this->hasMany(BarangStok::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
