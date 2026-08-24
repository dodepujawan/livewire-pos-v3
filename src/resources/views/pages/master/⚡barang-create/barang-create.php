<?php

use App\Models\Barang;
use App\Models\BarangSatuan;
use Livewire\Component;

new class extends Component
{
    protected array $additionalPermissions = [
        'master.barang.delete',
        'master.barang.export',
    ];
    public string $createBarangKode = '';
    public string $createBarangNama = '';
    public int $createBarangStok = 0;
    public $createBarangHargaBeli = 0;
    public int $createDefaultSatuanIndex = 0;
    public array $createBarangSatuanRows = [
        [
            'nama_satuan' => '',
            'konversi' => 1,
            'harga_jual' => 0,
            'harga_beli' => 0,
        ]
    ];

    public function addBarangSatuanRow()
    {
        $this->createBarangSatuanRows[] = [
            'nama_satuan' => '',
            'konversi' => 1,
            'harga_jual' => 0,
            'harga_beli' => 0,
        ];
    }

    public function removeBarangSatuanRow(int $rowIndex)
    {
        if (count($this->createBarangSatuanRows) <= 1) {
            return;
        }
        unset(
            $this->createBarangSatuanRows[$rowIndex]
        );
        $this->createBarangSatuanRows = array_values(
            $this->createBarangSatuanRows
        );
        if ($this->createDefaultSatuanIndex >= count($this->createBarangSatuanRows)) {
            $this->createDefaultSatuanIndex = 0;
        }
    }

    public function saveBarang()
    {
        $this->validate([
            'createBarangKode' => 'required|unique:barang,kode_barang',
            'createBarangNama' => 'required|min:3',
            'createBarangStok' => 'required|numeric|min:0',
            'createBarangHargaBeli' => 'nullable|numeric|min:0',

            'createBarangSatuanRows.*.nama_satuan' => 'required',
            'createBarangSatuanRows.*.konversi' => 'required|numeric|min:1',
            'createBarangSatuanRows.*.harga_jual' => 'required|numeric|min:0',
            'createBarangSatuanRows.*.harga_beli' => 'nullable|numeric|min:0',
        ]);

        $barang = Barang::create([
            'kode_barang' => strtoupper($this->createBarangKode),
            'nama_barang' => $this->createBarangNama,
            'stok' => $this->createBarangStok,
            'harga_beli' => $this->createBarangHargaBeli,
        ]);

        foreach ($this->createBarangSatuanRows as $index => $satuanRow) {
            BarangSatuan::create([
                'barang_id'   => $barang->id,
                'nama_satuan' => strtoupper($satuanRow['nama_satuan']),
                'konversi'    => $satuanRow['konversi'],
                'harga_jual'  => $satuanRow['harga_jual'],
                'harga_beli'  => $satuanRow['harga_beli'] ?? 0,
                'is_default'  => ($index === $this->createDefaultSatuanIndex),
            ]);
        }

        session()->flash(
            'success',
            'Barang berhasil ditambahkan'
        );

        return $this->redirect(
            route('master.barang.list'),
            navigate: true
        );
    }

    public function render()
    {
        return $this->view([])
            ->layout('layouts::app')
            ->title('Tambah Barang');
    }
};
