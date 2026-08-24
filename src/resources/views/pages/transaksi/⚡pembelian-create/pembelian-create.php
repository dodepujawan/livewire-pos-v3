<?php

use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\Cabang;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\BarangStok;
use App\Models\StokMutasi;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public string $pembelianNo = '';
    public string $pembelianTanggal = '';
    public int $pembelianCabangId = 0;
    public string $pembelianSupplier = '';
    public $pembelianTotal = 0;

    public array $listCabang = [];
    public array $cartItems = [];

    public string $itemKodeBarang = '';
    public int $itemBarangId = 0;
    public string $itemNamaBarang = '';
    public array $itemSatuanList = [];
    public int $itemBarangSatuanId = 0;
    public $itemQty = 1;
    public $itemHargaBeli = 0;
    public $itemSubtotal = 0;

    protected $rules = [
        'pembelianNo' => 'required|string|unique:pembelian,nomor_beli',
        'pembelianTanggal' => 'required|date',
        'pembelianCabangId' => 'required|exists:cabang,id',
        'pembelianSupplier' => 'required|string|min:3',
        'itemKodeBarang' => 'nullable|string',
        'itemBarangId' => 'required|exists:barang,id',
        'itemBarangSatuanId' => 'required|exists:barang_satuan,id',
        'itemQty' => 'required|numeric|min:0.01',
        'itemHargaBeli' => 'required|numeric|min:0',
    ];

    public function mount(): void
    {
        $this->pembelianTanggal = now()->format('Y-m-d');
        $this->generatePembelianNumber();
        $this->loadCabangList();
        $this->setDefaultCabang();
    }

    private function loadCabangList(): void
    {
        $this->listCabang = Cabang::where('is_aktif', true)
            ->orderBy('nama_cabang')
            ->get()
            ->mapWithKeys(fn($c) => [$c->id => $c->nama_cabang])
            ->toArray();
    }

    private function setDefaultCabang(): void
    {
        $user = Auth::user();
        if ($user && $user->cabang_id) {
            $this->pembelianCabangId = $user->cabang_id;
        } elseif (!empty($this->listCabang)) {
            $this->pembelianCabangId = array_key_first($this->listCabang);
        }
    }

    private function generatePembelianNumber(): void
    {
        $today = now()->format('Ymd');
        $lastPembelian = Pembelian::whereDate('tanggal', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPembelian) {
            $lastNumber = (int) substr($lastPembelian->nomor_beli, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $this->pembelianNo = 'PO-' . $today . '-' . $newNumber;
    }

    public function searchBarang(): void
    {
        $this->validate([
            'itemKodeBarang' => 'required|string',
        ]);

        $barang = Barang::where('kode_barang', strtoupper($this->itemKodeBarang))
            ->with('satuan')
            ->first();

        if (!$barang) {
            session()->flash('error', 'Barang tidak ditemukan');
            $this->resetItemForm();
            return;
        }

        $this->itemBarangId = $barang->id;
        $this->itemNamaBarang = $barang->nama_barang;
        $this->itemSatuanList = $barang->satuan->toArray();

        $defaultSatuan = $barang->satuan->firstWhere('konversi', 1);
        if (!$defaultSatuan) {
            $defaultSatuan = $barang->satuan->first();
        }

        if ($defaultSatuan) {
            $this->itemBarangSatuanId = $defaultSatuan->id;
            $this->itemHargaBeli = $barang->harga_beli ?? 0;
            $this->calculateItemSubtotal();
        }

        $this->dispatch('focus-qty');
    }

    public function updatedItemBarangSatuanId(): void
    {
        if ($this->itemBarangSatuanId > 0) {
            $barang = Barang::find($this->itemBarangId);
            if ($barang) {
                $this->itemHargaBeli = $barang->harga_beli ?? 0;
            }
        }
        $this->calculateItemSubtotal();
    }

    public function updatedItemQty(): void
    {
        $this->calculateItemSubtotal();
    }

    public function updatedItemHargaBeli(): void
    {
        $this->calculateItemSubtotal();
    }

    private function toFloat($value): float
    {
        return $value === '' || $value === null
            ? 0.0
            : (float) $value;
    }

    private function calculateItemSubtotal(): void
    {
        $harga = (float) $this->itemHargaBeli;
        $qty = (float) $this->itemQty;

        $this->itemSubtotal = $harga * $qty;
    }

    public function addToCart(): void
    {
        $this->validate([
            'itemBarangId' => 'required|exists:barang,id',
            'itemBarangSatuanId' => 'required|exists:barang_satuan,id',
            'itemQty' => 'required|numeric|min:0.01',
            'itemHargaBeli' => 'required|numeric|min:0',
        ]);

        $barang = Barang::find($this->itemBarangId);
        $satuan = BarangSatuan::find($this->itemBarangSatuanId);

        if (!$barang || !$satuan) {
            return;
        }

        $qty = (float) $this->itemQty;
        $hargaBeli = (float) $this->itemHargaBeli;
        $subtotal = (float) $this->itemSubtotal;

        $this->cartItems[] = [
            'barang_id' => $this->itemBarangId,
            'barang_satuan_id' => $this->itemBarangSatuanId,
            'nama_barang' => $barang->nama_barang,
            'nama_satuan' => $satuan->nama_satuan,
            'qty' => $qty,
            'harga_beli' => $hargaBeli,
            'subtotal' => $subtotal,
        ];

        $this->calculateGrandTotal();
        $this->resetItemForm();
        $this->dispatch('focus-kode-barang');
    }

    public function removeFromCart(int $index): void
    {
        unset($this->cartItems[$index]);
        $this->cartItems = array_values($this->cartItems);
        $this->calculateGrandTotal();
    }

    public function updatedCartItems(): void
    {
        foreach ($this->cartItems as $index => $item) {
            $qty = $this->toFloat($item['qty'] ?? 0);
            $hargaBeli = $this->toFloat($item['harga_beli'] ?? 0);

            if ($qty < 0.01) {
                $qty = 0.01;
                $this->cartItems[$index]['qty'] = 0.01;
            }

            $this->cartItems[$index]['subtotal'] = $hargaBeli * $qty;
        }

        $this->calculateGrandTotal();
    }

    private function calculateGrandTotal(): void
    {
        $this->pembelianTotal = 0;
        foreach ($this->cartItems as $item) {
            $this->pembelianTotal += (float) ($item['subtotal'] ?? 0);
        }
    }

    private function resetItemForm(): void
    {
        $this->itemKodeBarang = '';
        $this->itemBarangId = 0;
        $this->itemNamaBarang = '';
        $this->itemSatuanList = [];
        $this->itemBarangSatuanId = 0;
        $this->itemQty = 1;
        $this->itemHargaBeli = 0;
        $this->itemSubtotal = 0;
    }

    public function savePembelian(): void
    {
        $this->validate([
            'pembelianNo' => 'required|string|unique:pembelian,nomor_beli',
            'pembelianTanggal' => 'required|date',
            'pembelianCabangId' => 'required|exists:cabang,id',
            'pembelianSupplier' => 'required|string|min:3',
        ]);

        if (empty($this->cartItems)) {
            session()->flash('error', 'Keranjang pembelian kosong');
            return;
        }

        try {
            \DB::transaction(function () {
                $userId = Auth::id();

                $pembelian = Pembelian::create([
                    'nomor_beli' => $this->pembelianNo,
                    'cabang_id' => $this->pembelianCabangId,
                    'supplier' => $this->pembelianSupplier,
                    'tanggal' => $this->pembelianTanggal,
                    'total' => $this->pembelianTotal,
                    'status' => 'ORDER',
                ]);

                foreach ($this->cartItems as $item) {
                    PembelianDetail::create([
                        'pembelian_id' => $pembelian->id,
                        'barang_id' => $item['barang_id'],
                        'barang_satuan_id' => $item['barang_satuan_id'],
                        'qty' => $item['qty'],
                        'harga_beli' => $item['harga_beli'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }
            });

            session()->flash('success', 'Pembelian berhasil disimpan');

            $this->redirect(route('transaksi.pembelian.list'), navigate: true);
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return $this->view([])
        ->layout('layouts::app')
        ->title('Buat Pembelian');
    }
};
