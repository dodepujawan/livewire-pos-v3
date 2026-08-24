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
    public array $editBarangSatuanRows = [];
    public array $deleteBarangSatuanIds = [];

    public function mount($id){
        $barang = Barang::findOrFail($id);
        $this->editBarangId = $barang->id;
        $this->editBarangKode = $barang->kode_barang;
        $this->editBarangNama = $barang->nama_barang;
        $this->editBarangStok = $barang->stok;
        $this->editBarangSatuanRows =
        $barang->satuan
            ->map(function ($satuan) {
                return [
                    'id' => $satuan->id,
                    'nama_satuan' => $satuan->nama_satuan,
                    'konversi' => $satuan->konversi,
                    'harga_jual' => (int) $satuan->harga_jual,
                ];
            })
            ->toArray();
    }

    public function addEditBarangSatuanRow(){
        $this->editBarangSatuanRows[] = [
            'id' => null,
            'nama_satuan' => '',
            'konversi' => 1,
            'harga_jual' => 0,
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
    }

    public function updateBarang(){
        $this->validate([
            'editBarangKode' => 'required|unique:barang,kode_barang,' . $this->editBarangId,
            'editBarangNama' => 'required|min:3',
            'editBarangStok' => 'required|numeric|min:0',
            'editBarangSatuanRows.*.nama_satuan' => 'required',
            'editBarangSatuanRows.*.konversi' => 'required|numeric|min:1',
            'editBarangSatuanRows.*.harga_jual' => 'required|numeric|min:0',
        ]);

        $barang = Barang::findOrFail(
            $this->editBarangId
        );
        $barang->update([
            'kode_barang' => strtoupper($this->editBarangKode),
            'nama_barang' => $this->editBarangNama,
            'stok' => $this->editBarangStok,
        ]);

        foreach ($this->deleteBarangSatuanIds as $deleteId) {
            BarangSatuan::where('id', $deleteId)->delete();
        }

        foreach ($this->editBarangSatuanRows as $satuanRow) {
            if ($satuanRow['id']) {
                BarangSatuan::findOrFail($satuanRow['id'])->update([
                    'nama_satuan' => strtoupper($satuanRow['nama_satuan']),
                    'konversi'    => $satuanRow['konversi'],
                    'harga_jual'  => $satuanRow['harga_jual'],
                ]);
            } else {
                BarangSatuan::create([
                    'barang_id'   => $this->editBarangId,
                    'nama_satuan' => strtoupper($satuanRow['nama_satuan']),
                    'konversi'    => $satuanRow['konversi'],
                    'harga_jual'  => $satuanRow['harga_jual'],
                ]);
            }
        }

        session()->flash(
            'success',
            'Barang berhasil diperbarui'
        );
        return $this->redirect(
            route('barang-list'),
            navigate: true
        );
    }

    public function render(){
        return $this->view([])
            ->layout('layouts::app')
            ->title('Edit Barang');
    }
};

