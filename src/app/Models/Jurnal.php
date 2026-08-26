<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    protected $table = 'jurnal';

    protected $fillable = [
        'tanggal',
        'nomor_jurnal',
        'keterangan',
        'transaksi_id',
        'cabang_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function details()
    {
        return $this->hasMany(JurnalDetail::class);
    }
}
