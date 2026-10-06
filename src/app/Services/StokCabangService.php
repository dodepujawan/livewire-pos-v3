<?php

namespace App\Services;

use App\Models\BarangStok;
use App\Models\StokMutasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class StokCabangService
{
    public static function tersedia(int $barangId, int $cabangId): int
    {
        return (int) (BarangStok::query()
            ->where('barang_id', $barangId)
            ->where('cabang_id', $cabangId)
            ->value('stok') ?? 0);
    }

    public static function ubah(
        int $barangId,
        int $cabangId,
        int $perubahan,
        string $keterangan,
        ?string $tanggal = null,
        ?int $transaksiId = null,
        ?int $barangSatuanId = null,
        ?float $qtySatuan = null,
    ): int {
        return DB::transaction(function () use (
            $barangId,
            $cabangId,
            $perubahan,
            $keterangan,
            $tanggal,
            $transaksiId,
            $barangSatuanId,
            $qtySatuan,
        ): int {
            $stock = self::lockStock($barangId, $cabangId);
            return self::applyChange(
                $stock,
                $barangId,
                $cabangId,
                $perubahan,
                $keterangan,
                $tanggal,
                $transaksiId,
                $barangSatuanId,
                $qtySatuan,
            );
        });
    }

    public static function aturSaldo(
        int $barangId,
        int $cabangId,
        int $saldoBaru,
        string $keterangan,
        ?string $tanggal = null,
    ): int {
        if ($saldoBaru < 0) {
            throw new RuntimeException('Stok tidak boleh kurang dari nol.');
        }

        return DB::transaction(function () use ($barangId, $cabangId, $saldoBaru, $keterangan, $tanggal): int {
            $stock = self::lockStock($barangId, $cabangId);
            $perubahan = $saldoBaru - (int) $stock->stok;
            $keterangan = trim($keterangan);

            if ($perubahan !== 0 && $keterangan === '') {
                throw new RuntimeException('Isi alasan jika stok cabang berubah.');
            }
            if (Str::length($keterangan) > 255) {
                throw new RuntimeException('Alasan penyesuaian stok maksimal 255 karakter.');
            }

            return self::applyChange(
                $stock,
                $barangId,
                $cabangId,
                $perubahan,
                $keterangan,
                $tanggal,
                null,
                null,
                null,
            );
        });
    }

    public static function pindahkan(
        int $barangId,
        int $cabangAsalId,
        int $cabangTujuanId,
        int $qty,
        string $alasan,
    ): string {
        if ($cabangAsalId === $cabangTujuanId) {
            throw new RuntimeException('Cabang asal dan tujuan harus berbeda.');
        }
        if ($qty < 1) {
            throw new RuntimeException('Jumlah transfer minimal satu pcs.');
        }
        if (Str::length($alasan) > 150) {
            throw new RuntimeException('Alasan transfer maksimal 150 karakter.');
        }

        return DB::transaction(function () use (
            $barangId,
            $cabangAsalId,
            $cabangTujuanId,
            $qty,
            $alasan,
        ): string {
            $branchIds = [$cabangAsalId, $cabangTujuanId];
            sort($branchIds);

            $stocks = [];
            foreach ($branchIds as $branchId) {
                $stocks[$branchId] = self::lockStock($barangId, $branchId);
            }

            $reference = 'TRF-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5));
            $date = now()->toDateString();

            self::applyChange(
                $stocks[$cabangAsalId],
                $barangId,
                $cabangAsalId,
                -$qty,
                "Transfer {$reference} ke cabang {$cabangTujuanId}: {$alasan}",
                $date,
                null,
                null,
                null,
            );
            self::applyChange(
                $stocks[$cabangTujuanId],
                $barangId,
                $cabangTujuanId,
                $qty,
                "Transfer {$reference} dari cabang {$cabangAsalId}: {$alasan}",
                $date,
                null,
                null,
                null,
            );

            return $reference;
        });
    }

    private static function lockStock(int $barangId, int $cabangId): BarangStok
    {
        $stock = BarangStok::query()->firstOrCreate(
            [
                'barang_id' => $barangId,
                'cabang_id' => $cabangId,
            ],
            ['stok' => 0],
        );

        return BarangStok::query()
            ->whereKey($stock->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private static function applyChange(
        BarangStok $stock,
        int $barangId,
        int $cabangId,
        int $perubahan,
        string $keterangan,
        ?string $tanggal,
        ?int $transaksiId,
        ?int $barangSatuanId,
        ?float $qtySatuan,
    ): int {
        $newStock = (int) $stock->stok + $perubahan;
        if ($newStock < 0) {
            throw new RuntimeException('Stok tidak mencukupi di cabang yang dipilih.');
        }

        if ($perubahan !== 0) {
            $stock->update(['stok' => $newStock]);

            StokMutasi::create([
                'barang_id' => $barangId,
                'cabang_id' => $cabangId,
                'transaksi_id' => $transaksiId,
                'barang_satuan_id' => $barangSatuanId,
                'tanggal' => $tanggal ?? now()->toDateString(),
                'tipe' => $perubahan > 0 ? 'MASUK' : 'KELUAR',
                'qty' => abs($perubahan),
                'qty_satuan' => $qtySatuan,
                'keterangan' => $keterangan,
            ]);
        }

        return $newStock;
    }
}
