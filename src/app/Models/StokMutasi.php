<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasi';
    protected $fillable = [
        'barang_id',
        'tanggal',
        'tipe',
        'qty',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
    /** @use HasFactory<\Database\Factories\StokMutasiFactory> */
    use HasFactory;
}
