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
        return [[null, null, null, null, null, null, null, 'BARANG_BARU_V1']];
    }

    public function headings(): array
    {
        return [
            'kode_barang', 'nama_barang', 'stok', 'nama_satuan',
            'konversi', 'harga_jual', 'harga_beli', 'jenis_template',
        ];
    }

    public function title(): string
    {
        return 'Tambah Barang Baru';
    }
}
