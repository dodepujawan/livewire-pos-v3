<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasMutasi extends Model
{
    protected $table = 'kas_mutasi';

    protected $fillable = [
        'cabang_id',
        'tanggal',
        'tipe',
        'sumber',
        'transaksi_id',
        'jumlah',
        'saldo_akhir',
        'keterangan',
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
}
