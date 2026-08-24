<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'stok'
    ];

    public function satuan()
    {
        return $this->hasMany(
            BarangSatuan::class
        );
    }
}
