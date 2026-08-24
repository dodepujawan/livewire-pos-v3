<?php

use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\StokMutasi;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Livewire\Component;

new class extends Component
{
    public int $transaksiId;

    // Header
    public string $transNoInvoice = '';
    public string $transTanggal = '';
    public string $transCustomer = '';
    public $transGrandTotal = 0;

    // Cart
    public array $cartItems = [];

    // Single Item Form
    public string $itemKodeBarang = '';
    public int $itemBarangId = 0;
    public string $itemNamaBarang = '';
    public int $itemStok = 0;
    public array $itemSatuanList = [];
    public int $itemBarangSatuanId = 0;
    public $itemQty = 1;
    public $itemHarga = 0;
    public $itemDiskon = 0;
    public $itemSubtotal = 0;

    // Payment
    public $bayarNominal = 0;
    public $kembaliNominal = 0;

    protected $rules = [
        'transTanggal' => 'required|date',
        'transCustomer' => 'nullable|string',
        'itemKodeBarang' => 'nullable|string',
        'itemBarangId' => 'required|exists:barang,id',
        'itemBarangSatuanId' => 'required|exists:barang_satuan,id',
        'itemQty' => 'required|integer|min:1',
        'itemHarga' => 'required|numeric|min:0',
        'itemDiskon' => 'nullable|numeric|min:0',
        'bayarNominal' => 'required|numeric|min:0',
    ];

    public function mount(int $id): void
    {
        $this->transaksiId = $id;

        $transaksi = Transaksi::with(['details.barang', 'details.satuan'])
            ->findOrFail($id);

        $this->transNoInvoice = $transaksi->nomor_transaksi;
        $this->transTanggal = $transaksi->tanggal->format('Y-m-d');
        $this->transCustomer = $transaksi->customer;
        $this->transGrandTotal = (float) $transaksi->grand_total;

        // Load existing details into cart
        foreach ($transaksi->details as $detail) {
            $this->cartItems[] = [
                'barang_id' => $detail->barang_id,
                'barang_satuan_id' => $detail->barang_satuan_id,
                'nama_barang' => $detail->barang->nama_barang,
                'nama_satuan' => $detail->satuan->nama_satuan,
                'qty' => (int) $detail->qty,
                'harga' => $detail->harga,
                'diskon' => $detail->diskon,
                'subtotal' => $detail->subtotal,
                'qty_pcs' => $detail->qty_pcs,
            ];
        }

        // Calculate payment
        $this->bayarNominal = $this->transGrandTotal;
        $this->kembaliNominal = 0;
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
        $this->itemStok = $barang->stok;
        $this->itemSatuanList = $barang->satuan->toArray();

        // Auto-select satuan: prefer konversi = 1, else first available
        $defaultSatuan = $barang->satuan->firstWhere('konversi', 1);
        if (!$defaultSatuan) {
            $defaultSatuan = $barang->satuan->first();
        }

        if ($defaultSatuan) {
            $this->itemBarangSatuanId = $defaultSatuan->id;
            $this->itemHarga = $defaultSatuan->harga_jual;
            $this->calculateItemSubtotal();
        }

        $this->dispatch('focus-qty');
    }

    public function updatedItemBarangSatuanId(): void
    {
        if ($this->itemBarangSatuanId > 0) {
            $satuan = BarangSatuan::find($this->itemBarangSatuanId);
            if ($satuan) {
                $this->itemHarga = $satuan->harga_jual;
            }
        }
        $this->calculateItemSubtotal();
    }

    public function updatedItemQty(): void
    {
        $this->calculateItemSubtotal();
    }

    public function updatedItemDiskon(): void
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
        $harga = (float) $this->itemHarga;
        $qty = (int) $this->itemQty;
        $diskon = (float) $this->itemDiskon;

        $this->itemSubtotal = ($harga * $qty) - $diskon;
    }

    public function addToCart(): void
    {
        $this->validate([
            'itemBarangId' => 'required|exists:barang,id',
            'itemBarangSatuanId' => 'required|exists:barang_satuan,id',
            'itemQty' => 'required|integer|min:1',
        ]);

        $barang = Barang::find($this->itemBarangId);
        $satuan = BarangSatuan::find($this->itemBarangSatuanId);

        if (!$barang || !$satuan) {
            return;
        }

        $qtyPcs = (int) $this->itemQty * $satuan->konversi;

        // Check if same barang and satuan already in cart
        $existingIndex = null;
        foreach ($this->cartItems as $index => $item) {
            if ($item['barang_id'] === $this->itemBarangId && $item['barang_satuan_id'] === $this->itemBarangSatuanId) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Update existing item
            $newQty = (int) $this->cartItems[$existingIndex]['qty'] + (int) $this->itemQty;
            $newQtyPcs = $newQty * $satuan->konversi;

            if ($barang->stok < $newQtyPcs) {
                session()->flash('error', 'Total qty melebihi stok tersedia');
                return;
            }

            $this->cartItems[$existingIndex]['qty'] = $newQty;
            $this->cartItems[$existingIndex]['qty_pcs'] = $newQtyPcs;
            $this->cartItems[$existingIndex]['subtotal'] = $newQty * $satuan->harga_jual;
        } else {
            // Add new item
            $qty = (int) $this->itemQty;
            $diskon = (float) $this->itemDiskon;
            $subtotal = (float) $this->itemSubtotal;

            $this->cartItems[] = [
                'barang_id' => $this->itemBarangId,
                'barang_satuan_id' => $this->itemBarangSatuanId,
                'nama_barang' => $barang->nama_barang,
                'nama_satuan' => $satuan->nama_satuan,
                'qty' => $qty,
                'harga' => $satuan->harga_jual,
                'diskon' => $diskon,
                'subtotal' => $subtotal,
                'qty_pcs' => $qtyPcs,
            ];
        }

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
            $qty = (int) $this->toFloat($item['qty'] ?? 0);
            $diskon = $this->toFloat($item['diskon'] ?? 0);
            $harga = (float) ($item['harga'] ?? 0);

            if ($qty < 1) {
                $qty = 1;
                $this->cartItems[$index]['qty'] = 1;
            }

            $satuan = BarangSatuan::find($item['barang_satuan_id']);
            $qtyPcs = $satuan ? $qty * $satuan->konversi : $qty;

            $barang = Barang::find($item['barang_id']);
            if ($barang && $barang->stok < $qtyPcs) {
                session()->flash('error', 'Stok tidak mencukupi untuk ' . $item['nama_barang']);
                $maxQty = floor($barang->stok / ($satuan ? $satuan->konversi : 1));
                $this->cartItems[$index]['qty'] = max(1, $maxQty);
                $qty = (int) $this->cartItems[$index]['qty'];
                $qtyPcs = $satuan ? $qty * $satuan->konversi : $qty;
            }

            $subtotal = ($harga * $qty) - $diskon;
            $this->cartItems[$index]['subtotal'] = $subtotal;
            $this->cartItems[$index]['qty_pcs'] = $qtyPcs;
        }

        $this->calculateGrandTotal();
        
        // Recalculate kembaliNominal after grand total changes
        $bayar = (float) $this->bayarNominal;
        $grandTotal = (float) $this->transGrandTotal;
        $this->kembaliNominal = $bayar - $grandTotal;
    }

    private function calculateGrandTotal(): void
    {
        $this->transGrandTotal = 0;
        foreach ($this->cartItems as $item) {
            $subtotal = (float) ($item['subtotal'] ?? 0);
            $this->transGrandTotal += $subtotal;
        }
    }

    private function resetItemForm(): void
    {
        $this->itemKodeBarang = '';
        $this->itemBarangId = 0;
        $this->itemNamaBarang = '';
        $this->itemStok = 0;
        $this->itemSatuanList = [];
        $this->itemBarangSatuanId = 0;
        $this->itemQty = 1;
        $this->itemHarga = 0;
        $this->itemDiskon = 0;
        $this->itemSubtotal = 0;
    }

    public function updatedBayarNominal(): void
    {
        $bayar = (float) $this->bayarNominal;
        $grandTotal = (float) $this->transGrandTotal;

        $this->kembaliNominal = $bayar - $grandTotal;
    }

    public function saveTransaksi(): void
    {
        $this->validate([
            'transTanggal' => 'required|date',
            'bayarNominal' => 'required|numeric|min:' . $this->transGrandTotal,
        ]);

        if (empty($this->cartItems)) {
            session()->flash('error', 'Keranjang belanja kosong');
            return;
        }

        try {
            \DB::beginTransaction();

            // Step 1: Lock all affected barang
            $oldDetailIds = TransaksiDetail::where('transaksi_id', $this->transaksiId)
                ->pluck('barang_id')
                ->toArray();

            $newBarangIds = collect($this->cartItems)->pluck('barang_id')->toArray();

            $allBarangIds = array_unique(array_merge($oldDetailIds, $newBarangIds));

            Barang::whereIn('id', $allBarangIds)->lockForUpdate()->get();

            // Step 2: Restore old stock
            $oldDetails = TransaksiDetail::where('transaksi_id', $this->transaksiId)->get();
            foreach ($oldDetails as $detail) {
                Barang::where('id', $detail->barang_id)
                    ->increment('stok', $detail->qty_pcs);
            }

            // Step 3: Update transaksi header
            $transaksi = Transaksi::find($this->transaksiId);
            $transaksi->update([
                'tanggal' => $this->transTanggal,
                'customer' => $this->transCustomer,
                'grand_total' => $this->transGrandTotal,
            ]);

            // Step 4: Delete old details and insert new details
            TransaksiDetail::where('transaksi_id', $this->transaksiId)->delete();

            foreach ($this->cartItems as $item) {
                TransaksiDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $item['barang_id'],
                    'barang_satuan_id' => $item['barang_satuan_id'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'diskon' => $item['diskon'],
                    'subtotal' => $item['subtotal'],
                    'qty_pcs' => $item['qty_pcs'],
                ]);
            }

            // Step 5: Deduct new stock
            foreach ($this->cartItems as $item) {
                $barang = Barang::find($item['barang_id']);
                if ($barang) {
                    if ($barang->stok < $item['qty_pcs']) {
                        throw new \Exception('Stok tidak mencukupi untuk ' . $item['nama_barang']);
                    }
                    $barang->decrement('stok', $item['qty_pcs']);
                }
            }

            // Step 6: Update stock mutation
            // Note: Cannot delete old mutations by transaksi_id as column doesn't exist in stok_mutasi table
            // Only creating new mutations for edited transaction

            foreach ($this->cartItems as $item) {
                StokMutasi::create([
                    'barang_id' => $item['barang_id'],
                    'tanggal' => $this->transTanggal,
                    'tipe' => 'keluar',
                    'qty' => $item['qty_pcs'],
                    'keterangan' => 'Edit Transaksi ' . $this->transNoInvoice,
                ]);
            }

            \DB::commit();

            session()->flash('success', 'Transaksi berhasil diupdate');

            $this->redirect(route('transaksi-show', ['id' => $this->transaksiId]), navigate: true);
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return $this->view([])
        ->layout('layouts::app')
        ->title('Edit Transaksi');
    }
};