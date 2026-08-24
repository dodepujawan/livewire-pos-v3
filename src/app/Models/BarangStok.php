<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangStok extends Model
{
    protected $table = 'barang_stok';

    protected $fillable = [
        'barang_id',
        'cabang_id',
        'stok',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }
}
