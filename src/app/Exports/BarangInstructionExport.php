<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class BarangInstructionExport implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['PETUNJUK IMPORT BARANG'],
            [''],
            ['IMPORT BARANG BARU'],
            ['- Gunakan sheet Import Barang untuk mengisi data.'],
            ['- Satu baris mewakili satu satuan barang.'],
            ['- Barang multi-satuan memakai kode_barang yang sama.'],
            ['- Setiap barang wajib memiliki tepat satu is_default = 1.'],
            ['- Minimal satu satuan harus memiliki konversi = 1.'],
            ['- Mode ini hanya untuk kode barang yang belum terdaftar.'],
            [''],
            ['UPDATE BARANG EXISTING'],
            ['- Klik Export Data Barang dari aplikasi terlebih dahulu.'],
            ['- Edit file hasil export, jangan membuat format sendiri.'],
            ['- Untuk menambah satuan, tambahkan baris dengan kode_barang yang sama.'],
            ['- Upload menggunakan mode Update Barang Existing.'],
            ['- Stok tidak diubah melalui update Excel.'],
            [''],
            ['CONTOH BARANG MULTI-SATUAN'],
            ['BRG001 | Air Mineral | PCS | 1 | 3000 | 2000 | 1'],
            ['BRG001 | Air Mineral | BOX | 12 | 30000 | 24000 | 0'],
        ];
    }

    public function title(): string
    {
        return 'Petunjuk';
    }
}
