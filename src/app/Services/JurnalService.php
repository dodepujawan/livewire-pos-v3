<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Pembelian;
use App\Models\Transaksi;

class JurnalService
{
    public static function buatJurnalPenjualan(Transaksi $transaksi): void
    {
        $grandTotal = (float) $transaksi->grand_total;
        $totalHargaBeli = $transaksi->details->sum(fn($d) => (float) $d->harga_beli * (float) $d->qty);

        $nomorJurnal = self::generateNomorJurnal('JNL-SAL', $transaksi->tanggal);

        $jurnal = Jurnal::create([
            'tanggal' => $transaksi->tanggal,
            'nomor_jurnal' => $nomorJurnal,
            'keterangan' => 'Jurnal Penjualan ' . $transaksi->nomor_transaksi,
            'transaksi_id' => $transaksi->id,
            'cabang_id' => $transaksi->cabang_id,
        ]);

        $akunKas = Akun::where('kode_akun', '1001')->first();
        $akunPenjualan = Akun::where('kode_akun', '4001')->first();
        $akunHpp = Akun::where('kode_akun', '5001')->first();
        $akunPersediaan = Akun::where('kode_akun', '1003')->first();

        if ($akunKas && $akunPenjualan) {
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunKas->id,
                'debit' => $grandTotal,
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunPenjualan->id,
                'kredit' => $grandTotal,
            ]);
        }

        if ($akunHpp && $akunPersediaan && $totalHargaBeli > 0) {
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunHpp->id,
                'debit' => $totalHargaBeli,
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunPersediaan->id,
                'kredit' => $totalHargaBeli,
            ]);
        }
    }

    public static function buatJurnalPembelian(Pembelian $pembelian): void
    {
        $total = (float) $pembelian->total;

        $nomorJurnal = self::generateNomorJurnal('JNL-BEL', $pembelian->tanggal);

        $jurnal = Jurnal::create([
            'tanggal' => $pembelian->tanggal,
            'nomor_jurnal' => $nomorJurnal,
            'keterangan' => 'Jurnal Pembelian ' . $pembelian->nomor_beli,
            'cabang_id' => $pembelian->cabang_id,
        ]);

        $akunPersediaan = Akun::where('kode_akun', '1003')->first();
        $akunHutang = Akun::where('kode_akun', '2001')->first();

        if ($akunPersediaan && $akunHutang) {
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunPersediaan->id,
                'debit' => $total,
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunHutang->id,
                'kredit' => $total,
            ]);
        }
    }

    public static function buatJurnalRefund(Transaksi $transaksi): void
    {
        $grandTotal = (float) $transaksi->grand_total;
        $totalHargaBeli = $transaksi->details->sum(fn($d) => (float) $d->harga_beli * (float) $d->qty);

        $nomorJurnal = self::generateNomorJurnal('JNL-RFD', $transaksi->tanggal);

        $jurnal = Jurnal::create([
            'tanggal' => $transaksi->tanggal,
            'nomor_jurnal' => $nomorJurnal,
            'keterangan' => 'Jurnal Refund ' . $transaksi->nomor_transaksi,
            'transaksi_id' => $transaksi->id,
            'cabang_id' => $transaksi->cabang_id,
        ]);

        $akunKas = Akun::where('kode_akun', '1001')->first();
        $akunPenjualan = Akun::where('kode_akun', '4001')->first();
        $akunHpp = Akun::where('kode_akun', '5001')->first();
        $akunPersediaan = Akun::where('kode_akun', '1003')->first();

        if ($akunKas && $akunPenjualan) {
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunKas->id,
                'kredit' => $grandTotal,
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunPenjualan->id,
                'debit' => $grandTotal,
            ]);
        }

        if ($akunHpp && $akunPersediaan && $totalHargaBeli > 0) {
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunHpp->id,
                'kredit' => $totalHargaBeli,
            ]);
            JurnalDetail::create([
                'jurnal_id' => $jurnal->id,
                'akun_id' => $akunPersediaan->id,
                'debit' => $totalHargaBeli,
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
