<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hutang extends Model
{
    protected $table = 'hutang';

    protected $fillable = [
        'cabang_id',
        'pembelian_id',
        'supplier',
        'nomor_hutang',
        'tanggal',
        'jumlah',
        'sisa',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function pelunasan()
    {
        return $this->hasMany(Pelunasan::class, 'referensi_id');
    }
}
