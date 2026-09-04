<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaksi extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'deleted_at',
        'deleted_by',
        'delete_reason',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected $dates = [
        'deleted_at',
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

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
