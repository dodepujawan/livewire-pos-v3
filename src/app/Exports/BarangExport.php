<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BarangExport implements Export, FromCollection, WithHeadings, WithTitle
{
    public function collection(): Collection
    {
        return Barang::with('satuan')
            ->withSum('stokPerCabang as stok_total', 'stok')
            ->get()
            ->flatMap(function (Barang $barang): Collection {
                $satuans = $barang->satuan->sortBy('konversi')->values();
                $baseConversion = $satuans->first()?->konversi;

                return $satuans->map(fn ($satuan) => [
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'stok_total_semua_cabang' => $satuan->konversi === $baseConversion
                        ? (int) ($barang->stok_total ?? 0)
                        : null,
                    'nama_satuan' => $satuan->nama_satuan,
                    'konversi' => $satuan->konversi,
                    'harga_jual' => $satuan->harga_jual,
                    'harga_beli' => $satuan->harga_beli,
                ]);
            });
    }

    public function headings(): array
    {
        return [
            'kode_barang', 'nama_barang', 'stok_total_semua_cabang', 'nama_satuan',
            'konversi', 'harga_jual', 'harga_beli',
        ];
    }

    public function title(): string
    {
        return 'Edit Tambah Satuan';
    }
}
