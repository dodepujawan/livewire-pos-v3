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
        'customer',
        'grand_total',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(TransaksiDetail::class);
    }
    /** @use HasFactory<\Database\Factories\TransaksiFactory> */
    use HasFactory;
}
