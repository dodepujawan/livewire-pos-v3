<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangSatuan extends Model
{
    protected $table = 'barang_satuan';

    protected $fillable = [
        'barang_id',
        'nama_satuan',
        'konversi',
        'harga_jual',
        'harga_beli',
        'is_default',
    ];

    public function barang()
    {
        return $this->belongsTo(
            Barang::class
        );
    }
}
