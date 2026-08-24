<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    protected $table = 'transaksi_detail';

    protected $fillable = [
        'transaksi_id',
        'barang_id',
        'barang_satuan_id',
        'qty',
        'harga',
        'diskon',
        'subtotal',
        'qty_pcs',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function satuan()
    {
        return $this->belongsTo(BarangSatuan::class, 'barang_satuan_id');
    }
    /** @use HasFactory<\Database\Factories\TransaksiDetailFactory> */
    use HasFactory;
}
