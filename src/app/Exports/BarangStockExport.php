<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BarangStockExport implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private readonly int $cabangId)
    {
    }

    public function collection(): Collection
    {
        return Barang::query()
            ->select(['id', 'kode_barang', 'nama_barang'])
            ->withSum([
                'stokPerCabang as stok_sekarang' => fn ($query) => $query
                    ->where('cabang_id', $this->cabangId),
            ], 'stok')
            ->orderBy('kode_barang')
            ->get()
            ->map(fn (Barang $barang) => [
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'stok_sekarang' => (int) ($barang->stok_sekarang ?? 0),
                'stok_baru' => null,
                'alasan' => null,
            ]);
    }

    public function headings(): array
    {
        return ['kode_barang', 'nama_barang', 'stok_sekarang', 'stok_baru', 'alasan'];
    }

    public function title(): string
    {
        return 'Penyesuaian Stok';
    }
}
