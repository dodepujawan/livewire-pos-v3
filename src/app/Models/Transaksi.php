<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'cabang_id',
        'user_id',
        'customer',
        'status',
        'metode_bayar',
        'bayar',
        'kembali',
        'grand_total',
        'diskon_total',
        'pajak',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(TransaksiDetail::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function piutang()
    {
        return $this->hasOne(Piutang::class);
    }

    /** @use HasFactory<\Database\Factories\TransaksiFactory> */
    use HasFactory;
}
