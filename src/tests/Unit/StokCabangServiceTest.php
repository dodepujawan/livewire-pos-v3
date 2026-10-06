<?php

namespace Tests\Unit;

use App\Models\Barang;
use App\Models\BarangStok;
use App\Models\Cabang;
use App\Models\StokMutasi;
use App\Services\StokCabangService;
use RuntimeException;
use Tests\TestCase;

class StokCabangServiceTest extends TestCase
{
    public function test_stock_changes_and_mutations_are_isolated_by_branch(): void
    {
        $barang = Barang::create([
            'kode_barang' => 'TEST-STOCK-1',
            'nama_barang' => 'Barang Tes',
            'stok' => 0,
            'harga_beli' => 0,
        ]);
        $cabangA = Cabang::create([
            'kode_cabang' => 'TEST-A',
            'nama_cabang' => 'Cabang A',
            'is_aktif' => true,
        ]);
        $cabangB = Cabang::create([
            'kode_cabang' => 'TEST-B',
            'nama_cabang' => 'Cabang B',
            'is_aktif' => true,
        ]);

        StokCabangService::ubah($barang->id, $cabangA->id, 10, 'Stok awal');
        StokCabangService::ubah($barang->id, $cabangB->id, 4, 'Stok awal');
        StokCabangService::ubah($barang->id, $cabangA->id, -3, 'Penjualan');
        StokCabangService::aturSaldo($barang->id, $cabangA->id, 5, 'Hasil stok opname');

        $this->assertSame(5, StokCabangService::tersedia($barang->id, $cabangA->id));
        $this->assertSame(4, StokCabangService::tersedia($barang->id, $cabangB->id));
        $this->assertDatabaseHas('stok_mutasi', [
            'barang_id' => $barang->id,
            'cabang_id' => $cabangA->id,
            'tipe' => 'KELUAR',
            'qty' => 3,
            'keterangan' => 'Penjualan',
        ]);
        $this->assertDatabaseHas('stok_mutasi', [
            'barang_id' => $barang->id,
            'cabang_id' => $cabangA->id,
            'tipe' => 'KELUAR',
            'qty' => 2,
            'keterangan' => 'Hasil stok opname',
        ]);
        $totalBeforeTransfer = StokCabangService::tersedia($barang->id, $cabangA->id)
            + StokCabangService::tersedia($barang->id, $cabangB->id);
        $reference = StokCabangService::pindahkan($barang->id, $cabangA->id, $cabangB->id, 2, 'Permintaan cabang');

        $this->assertStringStartsWith('TRF-', $reference);
        $this->assertSame(3, StokCabangService::tersedia($barang->id, $cabangA->id));
        $this->assertSame(6, StokCabangService::tersedia($barang->id, $cabangB->id));
        $this->assertSame(
            $totalBeforeTransfer,
            StokCabangService::tersedia($barang->id, $cabangA->id)
                + StokCabangService::tersedia($barang->id, $cabangB->id),
        );
        $barangDenganTotal = Barang::query()
            ->withSum('stokPerCabang as stok_total', 'stok')
            ->findOrFail($barang->id);
        $this->assertSame(9, (int) $barangDenganTotal->stok_total);
        $this->assertSame(6, StokMutasi::where('barang_id', $barang->id)->count());
        $this->assertSame(2, BarangStok::where('barang_id', $barang->id)->count());
    }

    public function test_stock_cannot_become_negative(): void
    {
        $barang = Barang::create([
            'kode_barang' => 'TEST-STOCK-2',
            'nama_barang' => 'Barang Tes 2',
            'stok' => 0,
            'harga_beli' => 0,
        ]);
        $cabang = Cabang::create([
            'kode_cabang' => 'TEST-C',
            'nama_cabang' => 'Cabang Tes',
            'is_aktif' => true,
        ]);

        try {
            StokCabangService::ubah($barang->id, $cabang->id, -1, 'Penjualan');
            $this->fail('Seharusnya menolak perubahan stok yang membuat saldo negatif.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Stok tidak mencukupi di cabang yang dipilih.', $exception->getMessage());
        }

        $this->assertSame(0, StokCabangService::tersedia($barang->id, $cabang->id));
        $this->assertSame(0, StokMutasi::where('barang_id', $barang->id)->count());
    }
}
