<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BarangTemplateDataExport implements Export, FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'kode_barang', 'nama_barang', 'stok', 'nama_satuan',
            'konversi', 'harga_jual', 'harga_beli',
        ];
    }

    public function title(): string
    {
        return 'Tambah Barang Baru';
    }
}
