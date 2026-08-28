<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Hutang;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\KasMutasi;
use App\Models\Pelunasan;
use App\Models\Piutang;
use App\Models\Transaksi;

class PelunasanService
{
    public static function processPelunasanPiutang(Pelunasan $pelunasan, float $jumlah): void
    {
        $piutang = $pelunasan->piutang;
        if (! $piutang) {
            throw new \Exception('Pelunasan tidak terhubung ke piutang');
        }

        if ($jumlah > $piutang->sisa) {
            throw new \Exception('Jumlah pelunasan melebihi sisa piutang');
        }

        \DB::transaction(function () use ($pelunasan, $piutang, $jumlah) {
            // Update sisa piutang
            $newSisa = $piutang->sisa - $jumlah;
            $piutang->update([
                'sisa' => $newSisa,
                'status' => $newSisa <= 0 ? 'LUNAS' : 'BELUM_LUNAS',
            ]);

            // Insert kas_mutasi MASUK (uang masuk dari pelanggan)
            KasMutasi::create([
                'cabang_id' => $pelunasan->cabang_id,
                'tanggal' => $pelunasan->tanggal,
                'tipe' => 'MASUK',
                'sumber' => 'PELUNASAN_PIUTANG',
                'jumlah' => $jumlah,
                'keterangan' => 'Pelunasan Piutang ' . $piutang->nomor_piutang,
            ]);

            // Buat jurnal: Kas debet, Piutang kredit
            self::insertJurnalPelunasanPiutang($pelunasan, $jumlah);
        });
    }

    public static function processPelunasanHutang(Pelunasan $pelunasan, float $jumlah): void
    {
        $hutang = $pelunasan->hutang;
        if (! $hutang) {
            throw new \Exception('Pelunasan tidak terhubung ke hutang');
        }

        if ($jumlah > $hutang->sisa) {
            throw new \Exception('Jumlah pelunasan melebihi sisa hutang');
        }

        \DB::transaction(function () use ($pelunasan, $hutang, $jumlah) {
            // Update sisa hutang
            $newSisa = $hutang->sisa - $jumlah;
            $hutang->update([
                'sisa' => $newSisa,
                'status' => $newSisa <= 0 ? 'LUNAS' : 'BELUM_LUNAS',
            ]);

            // Insert kas_mutasi KELUAR (uang keluar ke supplier)
            KasMutasi::create([
                'cabang_id' => $pelunasan->cabang_id,
                'tanggal' => $pelunasan->tanggal,
                'tipe' => 'KELUAR',
                'sumber' => 'PELUNASAN_HUTANG',
                'jumlah' => $jumlah,
                'keterangan' => 'Pelunasan Hutang ' . $hutang->nomor_hutang,
            ]);

            // Buat jurnal: Hutang debet, Kas kredit
            self::insertJurnalPelunasanHutang($pelunasan, $jumlah);
        });
    }

    private static function insertJurnalPelunasanPiutang(Pelunasan $pelunasan, float $jumlah): void
    {
        $nomorJurnal = self::generateNomorJurnal('JNL-PLN', $pelunasan->tanggal);

        $jurnal = Jurnal::create([
            'tanggal' => $pelunasan->tanggal,
            'nomor_jurnal' => $nomorJurnal,
            'keterangan' => 'Jurnal Pelunasan Piutang ' . $pelunasan->piutang->nomor_piutang,
            'cabang_id' => $pelunasan->cabang_id,
        ]);

        $akunKas = Akun::where('kode_akun', '1001')->first();
        $akunPiutang = Akun::where('kode_akun', '1002')->first();

        if ($akunKas && $akunPiutang) {
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunKas->id,
                'debit' => $jumlah,
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunPiutang->id,
                'kredit' => $jumlah,
            ]);
        }
    }

    private static function insertJurnalPelunasanHutang(Pelunasan $pelunasan, float $jumlah): void
    {
        $nomorJurnal = self::generateNomorJurnal('JNL-PLN', $pelunasan->tanggal);

        $jurnal = Jurnal::create([
            'tanggal' => $pelunasan->tanggal,
            'nomor_jurnal' => $nomorJurnal,
            'keterangan' => 'Jurnal Pelunasan Hutang ' . $pelunasan->hutang->nomor_hutang,
            'cabang_id' => $pelunasan->cabang_id,
        ]);

        $akunHutang = Akun::where('kode_akun', '2001')->first();
        $akunKas = Akun::where('kode_akun', '1001')->first();

        if ($akunHutang && $akunKas) {
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunHutang->id,
                'debit' => $jumlah,
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunKas->id,
                'kredit' => $jumlah,
            ]);
        }
    }

    private static function generateNomorJurnal(string $prefix, \Illuminate\Support\Carbon $tanggal): string
    {
        $dateStr = $tanggal->format('Ymd');
        $lastJurnal = Jurnal::whereDate('tanggal', $tanggal)
            ->where('nomor_jurnal', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastJurnal) {
            $lastNumber = (int) substr($lastJurnal->nomor_jurnal, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $dateStr . '-' . $newNumber;
    }
}
