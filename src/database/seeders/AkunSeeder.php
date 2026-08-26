<?php

namespace Database\Seeders;

use App\Models\Akun;
use Illuminate\Database\Seeder;

class AkunSeeder extends Seeder
{
    public function run(): void
    {
        $akuns = [
            ['kode_akun' => '1001', 'nama_akun' => 'Kas', 'tipe' => 'ASET'],
            ['kode_akun' => '1002', 'nama_akun' => 'Piutang', 'tipe' => 'ASET'],
            ['kode_akun' => '1003', 'nama_akun' => 'Persediaan', 'tipe' => 'ASET'],
            ['kode_akun' => '2001', 'nama_akun' => 'Hutang', 'tipe' => 'UTANG'],
            ['kode_akun' => '3001', 'nama_akun' => 'Modal', 'tipe' => 'MODAL'],
            ['kode_akun' => '4001', 'nama_akun' => 'Penjualan', 'tipe' => 'PENDAPATAN'],
            ['kode_akun' => '5001', 'nama_akun' => 'HPP', 'tipe' => 'BEBAN'],
            ['kode_akun' => '6001', 'nama_akun' => 'Beban Operasional', 'tipe' => 'BEBAN'],
        ];

        foreach ($akuns as $akun) {
            Akun::firstOrCreate(
                ['kode_akun' => $akun['kode_akun']],
                $akun
            );
        }
    }
}
