<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    protected $table = 'piutang';

    protected $fillable = [
        'cabang_id',
        'transaksi_id',
        'customer',
        'nomor_piutang',
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

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function pelunasan()
    {
        return $this->hasMany(Pelunasan::class, 'referensi_id');
    }
}
