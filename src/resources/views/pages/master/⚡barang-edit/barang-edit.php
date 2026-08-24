<?php

use App\Models\Barang;
use App\Models\BarangSatuan;
use Livewire\Component;

new class extends Component
{
    public int $editBarangId;
    public string $editBarangKode = '';
    public string $editBarangNama = '';
    public int $editBarangStok = 0;
    public $editBarangHargaBeli = 0;
    public int $editDefaultSatuanIndex = 0;
    public array $editBarangSatuanRows = [];
    public array $deleteBarangSatuanIds = [];

    public function mount($id){
        $barang = Barang::findOrFail($id);
        $this->editBarangId = $barang->id;
        $this->editBarangKode = $barang->kode_barang;
        $this->editBarangNama = $barang->nama_barang;
        $this->editBarangStok = $barang->stok;
        $this->editBarangHargaBeli = $barang->harga_beli;
        $satuans = $barang->satuan;
        $defaultFound = false;
        $this->editBarangSatuanRows = $satuans->map(function ($satuan, $index) use (&$defaultFound) {
            if ($satuan->is_default && !$defaultFound) {
                $this->editDefaultSatuanIndex = $index;
                $defaultFound = true;
            }
            return [
                'id' => $satuan->id,
                'nama_satuan' => $satuan->nama_satuan,
                'konversi' => $satuan->konversi,
                'harga_jual' => (int) $satuan->harga_jual,
                'harga_beli' => (int) ($satuan->harga_beli ?? 0),
            ];
        })->toArray();
    }

    public function addEditBarangSatuanRow(){
        $this->editBarangSatuanRows[] = [
            'id' => null,
            'nama_satuan' => '',
            'konversi' => 1,
            'harga_jual' => 0,
            'harga_beli' => 0,
        ];
    }

    public function removeEditBarangSatuanRow(int $rowIndex){
        if (count($this->editBarangSatuanRows) <= 1) {
            return;
        }
        $row = $this->editBarangSatuanRows[$rowIndex];
        if (!empty($row['id'])) {
            $this->deleteBarangSatuanIds[] = $row['id'];
        }
        unset($this->editBarangSatuanRows[$rowIndex]);
        $this->editBarangSatuanRows = array_values($this->editBarangSatuanRows);
        if ($this->editDefaultSatuanIndex >= count($this->editBarangSatuanRows)) {
            $this->editDefaultSatuanIndex = 0;
        }
    }

    public function updateBarang(){
        $this->validate([
            'editBarangKode' => 'required|unique:barang,kode_barang,' . $this->editBarangId,
            'editBarangNama' => 'required|min:3',
            'editBarangStok' => 'required|numeric|min:0',
            'editBarangSatuanRows.*.nama_satuan' => 'required',
            'editBarangSatuanRows.*.konversi' => 'required|numeric|min:1',
            'editBarangSatuanRows.*.harga_jual' => 'required|numeric|min:0',
            'editBarangSatuanRows.*.harga_beli' => 'nullable|numeric|min:0',
        ]);

        $barang = Barang::findOrFail(
            $this->editBarangId
        );
        $barang->update([
            'kode_barang' => strtoupper($this->editBarangKode),
            'nama_barang' => $this->editBarangNama,
            'stok' => $this->editBarangStok,
            'harga_beli' => $this->editBarangHargaBeli,
        ]);

        foreach ($this->deleteBarangSatuanIds as $deleteId) {
            BarangSatuan::where('id', $deleteId)->delete();
        }

        foreach ($this->editBarangSatuanRows as $index => $satuanRow) {
            if ($satuanRow['id']) {
                BarangSatuan::findOrFail($satuanRow['id'])->update([
                    'nama_satuan' => strtoupper($satuanRow['nama_satuan']),
                    'konversi'    => $satuanRow['konversi'],
                    'harga_jual'  => $satuanRow['harga_jual'],
                    'harga_beli'  => $satuanRow['harga_beli'] ?? 0,
                    'is_default'  => ($index === $this->editDefaultSatuanIndex),
                ]);
            } else {
                BarangSatuan::create([
                    'barang_id'   => $this->editBarangId,
                    'nama_satuan' => strtoupper($satuanRow['nama_satuan']),
                    'konversi'    => $satuanRow['konversi'],
                    'harga_jual'  => $satuanRow['harga_jual'],
                    'harga_beli'  => $satuanRow['harga_beli'] ?? 0,
                    'is_default'  => ($index === $this->editDefaultSatuanIndex),
                ]);
            }
        }

        session()->flash(
            'success',
            'Barang berhasil diperbarui'
        );
        return $this->redirect(
            route('master.barang.list'),
            navigate: true
        );
    }

    public function render(){
        return $this->view([])
            ->layout('layouts::app')
            ->title('Edit Barang');
    }
};
