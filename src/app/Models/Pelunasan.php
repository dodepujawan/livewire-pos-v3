<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelunasan extends Model
{
    protected $table = 'pelunasan';

    protected $fillable = [
        'cabang_id',
        'jenis',
        'referensi_id',
        'tanggal',
        'jumlah',
        'metode_bayar',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function piutang()
    {
        return $this->belongsTo(Piutang::class, 'referensi_id');
    }

    public function hutang()
    {
        return $this->belongsTo(Hutang::class, 'referensi_id');
    }
}
