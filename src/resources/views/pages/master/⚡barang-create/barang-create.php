<?php

use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\Cabang;
use App\Services\StokCabangService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    public int $createBarangCabangId = 0;
    public array $listCabang = [];
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

    public function mount(): void
    {
        $this->listCabang = Cabang::query()
            ->where('is_aktif', true)
            ->orderBy('nama_cabang')
            ->pluck('nama_cabang', 'id')
            ->toArray();

        $userBranch = Auth::user()?->cabang_id;
        $this->createBarangCabangId = isset($this->listCabang[$userBranch])
            ? (int) $userBranch
            : (int) (array_key_first($this->listCabang) ?? 0);
    }

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
            'createBarangStok' => 'required|integer|min:0',
            'createBarangCabangId' => 'exclude_if:createBarangStok,0|required|exists:cabang,id,is_aktif,1',
            'createBarangHargaBeli' => 'nullable|numeric|min:0',

            'createBarangSatuanRows.*.nama_satuan' => 'required',
            'createBarangSatuanRows.*.konversi' => 'required|numeric|min:1',
            'createBarangSatuanRows.*.harga_jual' => 'required|numeric|min:0',
            'createBarangSatuanRows.*.harga_beli' => 'nullable|numeric|min:0',
        ]);

        if ($this->createBarangCabangId > 0 && ! Cabang::query()
            ->whereKey($this->createBarangCabangId)
            ->where('is_aktif', true)
            ->exists()) {
            $this->addError('createBarangCabangId', 'Pilih cabang yang masih aktif.');
            return;
        }

        DB::transaction(function (): void {
            $barang = Barang::create([
                'kode_barang' => strtoupper($this->createBarangKode),
                'nama_barang' => $this->createBarangNama,
                'stok' => 0,
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

            if ($this->createBarangCabangId > 0) {
                StokCabangService::ubah(
                    $barang->id,
                    $this->createBarangCabangId,
                    $this->createBarangStok,
                    'Stok awal barang baru',
                );
            }
        });

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
