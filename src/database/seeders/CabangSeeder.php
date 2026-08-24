<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\BarangStok;
use App\Models\Cabang;
use Illuminate\Database\Seeder;

class CabangSeeder extends Seeder
{
    public function run(): void
    {
        $cabang = Cabang::firstOrCreate(
            ['kode_cabang' => 'PUSAT'],
            [
                'nama_cabang' => 'Cabang Pusat',
                'alamat' => null,
                'is_aktif' => true,
            ]
        );

        $existingStok = BarangStok::where('cabang_id', $cabang->id)->exists();

        if (!$existingStok) {
            $barangs = Barang::all();
            foreach ($barangs as $barang) {
                BarangStok::create([
                    'barang_id' => $barang->id,
                    'cabang_id' => $cabang->id,
                    'stok' => $barang->stok,
                ]);
            }
        }
    }
}
