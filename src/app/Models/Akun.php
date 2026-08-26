<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    protected $table = 'akun';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe',
        'cabang_id',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function jurnalDetails()
    {
        return $this->hasMany(JurnalDetail::class);
    }
}
