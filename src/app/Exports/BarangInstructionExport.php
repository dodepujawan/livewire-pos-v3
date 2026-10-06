<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithTitle;

class BarangInstructionExport implements Export, FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['PETUNJUK IMPORT BARANG'],
            [''],
            ['IMPORT BARANG BARU'],
            ['- Gunakan sheet Tambah Barang Baru untuk mengisi data.'],
            ['- Satu baris mewakili satu satuan barang.'],
            ['- Barang multi-satuan memakai kode_barang yang sama.'],
            ['- Minimal satu satuan harus memiliki konversi = 1.'],
            ['- Sistem otomatis memilih konversi terkecil sebagai satuan default.'],
            ['- Isi stok pada baris konversi terkecil; baris satuan lain boleh dikosongkan.'],
            ['- Pilih cabang pada aplikasi saat upload; stok awal hanya masuk ke cabang tersebut.'],
            ['- Jika stok belum diketahui, kosongkan kolom stok. Stok dapat disesuaikan kemudian dari form barang dengan alasan.'],
            ['- Jangan ubah kolom jenis_template; sistem menggunakannya untuk memastikan file ini diproses sebagai barang baru.'],
            ['- Saat menambah baris barang/satuan, salin juga nilai jenis_template dari baris sebelumnya.'],
            ['- Mode ini hanya untuk kode barang yang belum terdaftar.'],
            [''],
            ['UPDATE BARANG EXISTING'],
            ['- Klik Export Data Barang dari aplikasi terlebih dahulu.'],
            ['- Edit file hasil export, jangan membuat format sendiri.'],
            ['- Untuk menambah satuan, tambahkan baris dengan kode_barang yang sama.'],
            ['- Upload menggunakan mode Update Barang Existing.'],
            ['- Stok tidak diubah melalui update Excel.'],
            ['- Kode barang boleh memakai format apa pun, tetapi harus unik.'],
            ['- Jika memakai formula untuk kode, ubah hasilnya menjadi nilai dengan Paste Special > Values Only.'],
            [''],
            ['CONTOH BARANG MULTI-SATUAN'],
            ['BRG001 | Air Mineral | 100 | PCS | 1 | 3000 | 2000'],
            ['BRG001 | Air Mineral | kosong | BOX | 12 | 30000 | 24000'],
        ];
    }

    public function title(): string
    {
        return 'Cara Penggunaan';
    }
}
