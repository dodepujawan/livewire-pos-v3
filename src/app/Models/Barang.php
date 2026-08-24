<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'stok',
        'harga_beli',
    ];

    public function satuan()
    {
        return $this->hasMany(BarangSatuan::class);
    }

    public function stokPerCabang()
    {
        return $this->hasMany(BarangStok::class);
    }

    public function stokDiCabang(int $cabangId): int
    {
        $record = $this->stokPerCabang()->where('cabang_id', $cabangId)->first();
        return $record ? $record->stok : 0;
    }
}
