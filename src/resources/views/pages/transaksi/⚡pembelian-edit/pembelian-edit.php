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
    protected array $additionalPermissions = [
        'transaksi.pembelian.receive',
        'transaksi.pembelian.cancel',
    ];

    public int $pembelianId;

    public string $pembelianNo = '';
    public string $pembelianTanggal = '';
    public int $pembelianCabangId = 0;
    public string $pembelianSupplier = '';
    public $pembelianTotal = 0;
    public $pembelianPajak = 0;
    public string $pembelianStatus = 'ORDER';

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

    protected function rules(): array
    {
        return [
            'pembelianNo' => 'required|string|unique:pembelian,nomor_beli,' . $this->pembelianId,
            'pembelianTanggal' => 'required|date',
            'pembelianCabangId' => 'required|exists:cabang,id',
            'pembelianSupplier' => 'required|string|min:3',
            'itemKodeBarang' => 'nullable|string',
            'itemBarangId' => 'required|exists:barang,id',
            'itemBarangSatuanId' => 'required|exists:barang_satuan,id',
            'itemQty' => 'required|numeric|min:0.01',
            'itemHargaBeli' => 'required|numeric|min:0',
        ];
    }

    public function mount(int $id): void
    {
        $this->pembelianId = $id;

        $pembelian = Pembelian::with(['details.barang', 'details.satuan', 'cabang'])
            ->findOrFail($id);

        $this->pembelianNo = $pembelian->nomor_beli;
        $this->pembelianTanggal = $pembelian->tanggal->format('Y-m-d');
        $this->pembelianCabangId = $pembelian->cabang_id;
        $this->pembelianSupplier = $pembelian->supplier;
        $this->pembelianTotal = (float) $pembelian->total;
        $this->pembelianPajak = (float) ($pembelian->pajak ?? 0);
        $this->pembelianStatus = $pembelian->status;

        $this->loadCabangList();

        foreach ($pembelian->details as $detail) {
            $this->cartItems[] = [
                'id' => $detail->id,
                'barang_id' => $detail->barang_id,
                'barang_satuan_id' => $detail->barang_satuan_id,
                'nama_barang' => $detail->barang->nama_barang ?? '',
                'nama_satuan' => $detail->satuan->nama_satuan ?? '',
                'qty' => (float) $detail->qty,
                'harga_beli' => (float) $detail->harga_beli,
                'subtotal' => (float) $detail->subtotal,
            ];
        }
    }

    private function loadCabangList(): void
    {
        $this->listCabang = Cabang::where('is_aktif', true)
            ->orderBy('nama_cabang')
            ->get()
            ->mapWithKeys(fn($c) => [$c->id => $c->nama_cabang])
            ->toArray();
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
            'id' => null,
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

    public function updatePembelian(): void
    {
        if ($this->pembelianStatus !== 'ORDER') {
            session()->flash('error', 'Pembelian sudah diproses, tidak bisa diedit');
            return;
        }

        $this->validate([
            'pembelianNo' => 'required|string|unique:pembelian,nomor_beli,' . $this->pembelianId,
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
                $pembelian = Pembelian::findOrFail($this->pembelianId);
                $pembelian->update([
                    'nomor_beli' => $this->pembelianNo,
                    'cabang_id' => $this->pembelianCabangId,
                    'supplier' => $this->pembelianSupplier,
                    'tanggal' => $this->pembelianTanggal,
                    'total' => $this->pembelianTotal,
                    'pajak' => $this->pembelianPajak,
                ]);

                PembelianDetail::where('pembelian_id', $pembelian->id)->delete();

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

            session()->flash('success', 'Pembelian berhasil diupdate');

            $this->redirect(route('transaksi.pembelian.list'), navigate: true);
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function receivePembelian(): void
    {
        $pembelian = Pembelian::findOrFail($this->pembelianId);

        if ($pembelian->status !== 'ORDER') {
            session()->flash('error', 'Pembelian sudah diproses');
            return;
        }

        try {
            \DB::transaction(function () use ($pembelian) {
                $pembelian->update(['status' => 'TERIMA']);

                foreach ($pembelian->details as $detail) {
                    $barang = Barang::find($detail->barang_id);
                    if ($barang) {
                        $qtyPcs = (float) $detail->qty * ($detail->satuan->konversi ?? 1);

                        StokMutasi::create([
                            'barang_id' => $detail->barang_id,
                            'cabang_id' => $pembelian->cabang_id,
                            'tanggal' => $pembelian->tanggal,
                            'tipe' => 'MASUK',
                            'qty' => $qtyPcs,
                            'qty_satuan' => $detail->qty,
                            'keterangan' => 'Pembelian ' . $pembelian->nomor_beli,
                        ]);

                        BarangStok::updateOrCreate(
                            [
                                'barang_id' => $detail->barang_id,
                                'cabang_id' => $pembelian->cabang_id,
                            ],
                            ['stok' => ($barang->stok ?? 0) + $qtyPcs]
                        );

                        $barang->update([
                            'stok' => ($barang->stok ?? 0) + $qtyPcs,
                            'harga_beli' => $detail->harga_beli,
                        ]);
                    }
                }
            });

            \App\Services\JurnalService::buatJurnalPembelian($pembelian);

            session()->flash('success', 'Pembelian berhasil diterima');

            $this->redirect(route('transaksi.pembelian.list'), navigate: true);
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function cancelPembelian(): void
    {
        $pembelian = Pembelian::findOrFail($this->pembelianId);

        if ($pembelian->status === 'BATAL') {
            session()->flash('error', 'Pembelian sudah dibatalkan');
            return;
        }

        try {
            \DB::transaction(function () use ($pembelian) {
                if ($pembelian->status === 'TERIMA') {
                    foreach ($pembelian->details as $detail) {
                        $barang = Barang::find($detail->barang_id);
                        if ($barang) {
                            $qtyPcs = (float) $detail->qty * ($detail->satuan->konversi ?? 1);

                            StokMutasi::create([
                                'barang_id' => $detail->barang_id,
                                'cabang_id' => $pembelian->cabang_id,
                                'tanggal' => now(),
                                'tipe' => 'KELUAR',
                                'qty' => $qtyPcs,
                                'qty_satuan' => $detail->qty,
                                'keterangan' => 'Cancel Pembelian ' . $pembelian->nomor_beli,
                            ]);

                            BarangStok::where([
                                'barang_id' => $detail->barang_id,
                                'cabang_id' => $pembelian->cabang_id,
                            ])->decrement('stok', $qtyPcs);

                            $barang->decrement('stok', $qtyPcs);
                        }
                    }
                }

                $pembelian->update(['status' => 'BATAL']);
            });

            session()->flash('success', 'Pembelian berhasil dibatalkan');

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
        ->title('Edit Pembelian');
    }
};
