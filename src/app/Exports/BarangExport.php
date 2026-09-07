<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BarangExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection(): Collection
    {
        return Barang::with('satuan')->get()->flatMap(function (Barang $barang): Collection {
            return $barang->satuan->map(fn ($satuan) => [
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'stok' => $barang->stok,
                'nama_satuan' => $satuan->nama_satuan,
                'konversi' => $satuan->konversi,
                'harga_jual' => $satuan->harga_jual,
                'harga_beli' => $satuan->harga_beli,
                'is_default' => $satuan->is_default ? 1 : 0,
            ]);
        });
    }

    public function headings(): array
    {
        return [
            'kode_barang', 'nama_barang', 'stok', 'nama_satuan',
            'konversi', 'harga_jual', 'harga_beli', 'is_default',
        ];
    }

    public function title(): string
    {
        return 'Data Barang';
    }
}
