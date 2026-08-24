<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasi';
    protected $fillable = [
        'barang_id',
        'cabang_id',
        'transaksi_id',
        'barang_satuan_id',
        'tanggal',
        'tipe',
        'qty',
        'qty_satuan',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function barangSatuan()
    {
        return $this->belongsTo(BarangSatuan::class, 'barang_satuan_id');
    }
    /** @use HasFactory<\Database\Factories\StokMutasiFactory> */
    use HasFactory;
}
