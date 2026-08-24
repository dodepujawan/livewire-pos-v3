<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'pembelian';

    protected $fillable = [
        'nomor_beli',
        'cabang_id',
        'supplier',
        'tanggal',
        'total',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function details()
    {
        return $this->hasMany(PembelianDetail::class);
    }
}
