<?php

use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\Cabang;
use App\Services\StokCabangService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    public int $editBarangId;
    public string $editBarangKode = '';
    public string $editBarangNama = '';
    public int $editBarangStok = 0;
    #[Locked]
    public int $editBarangStokSaatEdit = 0;
    public int $editBarangCabangId = 0;
    public string $editBarangStokAlasan = '';
    public array $listCabang = [];
    public int $transferStokDariCabangId = 0;
    public int $transferStokKeCabangId = 0;
    public int $transferStokQty = 1;
    public string $transferStokAlasan = '';
    public $editBarangHargaBeli = 0;
    public int $editDefaultSatuanIndex = 0;
    public array $editBarangSatuanRows = [];
    public array $deleteBarangSatuanIds = [];

    public function mount($id){
        $barang = Barang::findOrFail($id);
        $this->editBarangId = $barang->id;
        $this->editBarangKode = $barang->kode_barang;
        $this->editBarangNama = $barang->nama_barang;
        $this->editBarangHargaBeli = $barang->harga_beli;
        $this->listCabang = Cabang::query()
            ->where('is_aktif', true)
            ->orderBy('nama_cabang')
            ->pluck('nama_cabang', 'id')
            ->toArray();
        $userBranch = Auth::user()?->cabang_id;
        $this->editBarangCabangId = isset($this->listCabang[$userBranch])
            ? (int) $userBranch
            : (int) (array_key_first($this->listCabang) ?? 0);
        $this->transferStokDariCabangId = $this->editBarangCabangId;
        $this->transferStokKeCabangId = (int) (collect(array_keys($this->listCabang))
            ->first(fn ($id) => (int) $id !== $this->transferStokDariCabangId) ?? 0);
        $this->editBarangStok = StokCabangService::tersedia($barang->id, $this->editBarangCabangId);
        $this->editBarangStokSaatEdit = $this->editBarangStok;
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

    public function updatedEditBarangCabangId(): void
    {
        $this->editBarangStok = StokCabangService::tersedia($this->editBarangId, $this->editBarangCabangId);
        $this->editBarangStokSaatEdit = $this->editBarangStok;
        $this->editBarangStokAlasan = '';
    }

    public function pindahkanStok(): void
    {
        abort_unless(auth()->user()->can('master.barang.update'), 403);

        if ($this->editBarangStok !== $this->editBarangStokSaatEdit) {
            $this->addError('editBarangStok', 'Simpan penyesuaian stok sebelum melakukan transfer.');
            return;
        }

        $this->validate([
            'transferStokDariCabangId' => 'required|exists:cabang,id,is_aktif,1',
            'transferStokKeCabangId' => 'required|exists:cabang,id,is_aktif,1',
            'transferStokQty' => 'required|integer|min:1',
            'transferStokAlasan' => 'required|string|min:3|max:150',
        ]);

        if ($this->transferStokDariCabangId === $this->transferStokKeCabangId) {
            $this->addError('transferStokKeCabangId', 'Cabang tujuan harus berbeda dari cabang asal.');
            return;
        }

        try {
            $reference = StokCabangService::pindahkan(
                $this->editBarangId,
                $this->transferStokDariCabangId,
                $this->transferStokKeCabangId,
                $this->transferStokQty,
                trim($this->transferStokAlasan),
            );
        } catch (\RuntimeException $exception) {
            $this->addError('transferStokQty', $exception->getMessage());
            return;
        }

        if (in_array($this->editBarangCabangId, [$this->transferStokDariCabangId, $this->transferStokKeCabangId], true)) {
            $this->editBarangStok = StokCabangService::tersedia($this->editBarangId, $this->editBarangCabangId);
            $this->editBarangStokSaatEdit = $this->editBarangStok;
        }

        $this->transferStokAlasan = '';
        session()->flash('success', "Stok berhasil dipindahkan. Nomor referensi: {$reference}");
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
            'editBarangStok' => 'required|integer|min:0',
            'editBarangCabangId' => 'required|exists:cabang,id,is_aktif,1',
            'editBarangStokAlasan' => 'nullable|string|max:255',
            'editBarangSatuanRows.*.nama_satuan' => 'required',
            'editBarangSatuanRows.*.konversi' => 'required|numeric|min:1',
            'editBarangSatuanRows.*.harga_jual' => 'required|numeric|min:0',
            'editBarangSatuanRows.*.harga_beli' => 'nullable|numeric|min:0',
        ]);

        $stokDiubah = $this->editBarangStok !== $this->editBarangStokSaatEdit;
        if ($stokDiubah && trim($this->editBarangStokAlasan) === '') {
            $this->addError('editBarangStokAlasan', 'Isi alasan jika jumlah stok cabang diubah.');
            return;
        }

        DB::transaction(function (): void {
            $barang = Barang::findOrFail($this->editBarangId);
            $barang->update([
                'kode_barang' => strtoupper($this->editBarangKode),
                'nama_barang' => $this->editBarangNama,
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

            if ($this->editBarangStok !== $this->editBarangStokSaatEdit) {
                StokCabangService::aturSaldo(
                    $this->editBarangId,
                    $this->editBarangCabangId,
                    $this->editBarangStok,
                    trim($this->editBarangStokAlasan),
                );
            }
        });

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
