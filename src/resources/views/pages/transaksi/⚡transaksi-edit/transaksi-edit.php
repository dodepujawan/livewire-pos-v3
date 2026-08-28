<?php

use App\Models\Barang;
use App\Models\BarangStok;
use App\Models\BarangSatuan;
use App\Models\Cabang;
use App\Models\KasMutasi;
use App\Models\StokMutasi;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Livewire\Component;

new class extends Component
{
    protected array $additionalPermissions = [
        'transaksi.penjualan.cancel',
    ];

    public int $transaksiId;

    // Header
    public string $transNoInvoice = '';
    public string $transTanggal = '';
    public string $transCustomer = '';
    public $transGrandTotal = 0;
    public int $transCabangId = 0;
    public string $transMetodeBayar = 'TUNAI';
    public string $transStatus = 'SELESAI';
    public $transBayar = 0;
    public $transKembali = 0;
    public $transDiskonTotal = 0;
    public $transPajak = 0;
    public string $transCatatan = '';

    // Cabang list
    public array $listCabang = [];

    // Cart
    public array $cartItems = [];

    // Modal
    public bool $showBayarModal = false;

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

    protected $rules = [
        'transTanggal' => 'required|date',
        'transCustomer' => 'nullable|string',
        'transCabangId' => 'required|exists:cabang,id',
        'transMetodeBayar' => 'required|in:TUNAI,TRANSFER,QRIS',
        'transBayar' => 'required|numeric|min:0',
        'transPajak' => 'nullable|numeric|min:0',
        'itemKodeBarang' => 'nullable|string',
        'itemBarangId' => 'required|exists:barang,id',
        'itemBarangSatuanId' => 'required|exists:barang_satuan,id',
        'itemQty' => 'required|integer|min:1',
        'itemHarga' => 'required|numeric|min:0',
        'itemDiskon' => 'nullable|numeric|min:0',
    ];

    public function mount(int $id): void
    {
        $this->transaksiId = $id;

        $transaksi = Transaksi::with(['details.barang', 'details.satuan'])
            ->findOrFail($id);

        $this->transNoInvoice = $transaksi->nomor_transaksi;
        $this->transTanggal = $transaksi->tanggal->format('Y-m-d');
        $this->transCustomer = $transaksi->customer;
        $this->transCabangId = $transaksi->cabang_id;
        $this->transMetodeBayar = $transaksi->metode_bayar;
        $this->transStatus = $transaksi->status;
        $this->transBayar = (float) $transaksi->bayar;
        $this->transKembali = (float) $transaksi->kembali;
        $this->transGrandTotal = (float) $transaksi->grand_total;
        $this->transDiskonTotal = (float) $transaksi->diskon_total;
        $this->transPajak = (float) $transaksi->pajak;
        $this->transCatatan = $transaksi->catatan;

        $this->loadCabangList();

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

        $this->calculateGrandTotal();
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
        $this->itemStok = $barang->stok;
        $this->itemSatuanList = $barang->satuan->toArray();

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

    public function updatedTransBayar(): void
    {
        $this->transKembali = (float) $this->transBayar - (float) $this->transGrandTotal;
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

        $existingIndex = null;
        foreach ($this->cartItems as $index => $item) {
            if ($item['barang_id'] === $this->itemBarangId && $item['barang_satuan_id'] === $this->itemBarangSatuanId) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
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

        $bayar = (float) $this->transBayar;
        $grandTotal = (float) $this->transGrandTotal;
        $this->transKembali = $bayar - $grandTotal;
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

    public function openBayarModal(): void
    {
        $this->showBayarModal = true;
    }

    public function saveTransaksi(): void
    {
        $this->validate([
            'transTanggal' => 'required|date',
            'transCabangId' => 'required|exists:cabang,id',
            'transMetodeBayar' => 'required|in:TUNAI,TRANSFER,QRIS',
            'transBayar' => 'required|numeric|min:0',
            'transPajak' => 'nullable|numeric|min:0',
        ]);

        if ($this->transStatus === 'SELESAI' && (float) $this->transBayar < (float) $this->transGrandTotal) {
            session()->flash('error', 'Bayar harus minimal sama dengan Grand Total untuk transaksi Selesai');
            return;
        }

        if (empty($this->cartItems)) {
            session()->flash('error', 'Keranjang belanja kosong');
            return;
        }

        try {
            \DB::beginTransaction();

            // Restore old stock
            $oldDetails = TransaksiDetail::where('transaksi_id', $this->transaksiId)->get();
            foreach ($oldDetails as $detail) {
                Barang::where('id', $detail->barang_id)
                    ->increment('stok', $detail->qty_pcs);
            }

            // Update transaksi header
            $transaksi = Transaksi::find($this->transaksiId);
            $transaksi->update([
                'tanggal' => $this->transTanggal,
                'cabang_id' => $this->transCabangId,
                'customer' => $this->transCustomer,
                'status' => $this->transStatus,
                'metode_bayar' => $this->transMetodeBayar,
                'bayar' => $this->transBayar,
                'kembali' => $this->transKembali,
                'grand_total' => $this->transGrandTotal,
                'diskon_total' => $this->transDiskonTotal,
                'pajak' => $this->transPajak,
                'catatan' => $this->transCatatan,
            ]);

            // Delete old details and insert new details
            TransaksiDetail::where('transaksi_id', $this->transaksiId)->delete();

            foreach ($this->cartItems as $item) {
                $barang = Barang::find($item['barang_id']);
                $satuan = BarangSatuan::find($item['barang_satuan_id']);
                $hargaBeli = $barang ? ($barang->harga_beli ?? 0) : 0;

                TransaksiDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $item['barang_id'],
                    'barang_satuan_id' => $item['barang_satuan_id'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'diskon' => $item['diskon'],
                    'subtotal' => $item['subtotal'],
                    'qty_pcs' => $item['qty_pcs'],
                    'harga_beli' => $hargaBeli,
                    'nama_barang' => $item['nama_barang'],
                    'nama_satuan' => $item['nama_satuan'],
                ]);
            }

            // Deduct new stock
            foreach ($this->cartItems as $item) {
                $barang = Barang::find($item['barang_id']);
                if ($barang) {
                    if ($barang->stok < $item['qty_pcs']) {
                        throw new \Exception('Stok tidak mencukupi untuk ' . $item['nama_barang']);
                    }
                    $barang->decrement('stok', $item['qty_pcs']);
                }
            }

            // Update stock mutation
            foreach ($this->cartItems as $item) {
                StokMutasi::create([
                    'barang_id' => $item['barang_id'],
                    'cabang_id' => $this->transCabangId,
                    'transaksi_id' => $transaksi->id,
                    'barang_satuan_id' => $item['barang_satuan_id'],
                    'tanggal' => $this->transTanggal,
                    'tipe' => 'KELUAR',
                    'qty' => $item['qty_pcs'],
                    'qty_satuan' => $item['qty'],
                    'keterangan' => 'Edit Transaksi ' . $this->transNoInvoice,
                ]);
            }

            // Update payment entries if payment fields changed
            $oldBayar = (float) $transaksi->getOriginal('bayar');
            $oldStatus = $transaksi->getOriginal('status');
            $oldMetodeBayar = $transaksi->getOriginal('metode_bayar');

            if ($oldBayar != $this->transBayar || $oldStatus != $this->transStatus || $oldMetodeBayar != $this->transMetodeBayar) {
                // Remove old kas_mutasi entries for this transaksi
                KasMutasi::where('transaksi_id', $this->transaksiId)->where('sumber', 'PENJUALAN')->delete();

                if ($this->transMetodeBayar === 'TUNAI' && $this->transStatus === 'SELESAI') {
                    KasMutasi::create([
                        'cabang_id' => $this->transCabangId,
                        'tanggal' => $this->transTanggal,
                        'tipe' => 'MASUK',
                        'sumber' => 'PENJUALAN',
                        'transaksi_id' => $transaksi->id,
                        'jumlah' => $this->transBayar,
                        'keterangan' => 'Pembayaran tunai ' . $this->transNoInvoice,
                    ]);

                    if ($this->transKembali > 0) {
                        KasMutasi::create([
                            'cabang_id' => $this->transCabangId,
                            'tanggal' => $this->transTanggal,
                            'tipe' => 'KELUAR',
                            'sumber' => 'PENJUALAN',
                            'transaksi_id' => $transaksi->id,
                            'jumlah' => $this->transKembali,
                            'keterangan' => 'Kembalian ' . $this->transNoInvoice,
                        ]);
                    }
                }
            }

            // Handle piutang
            if ($this->transStatus === 'PIUTANG') {
                \App\Models\Piutang::updateOrCreate(
                    ['transaksi_id' => $transaksi->id],
                    [
                        'cabang_id' => $this->transCabangId,
                        'customer' => $this->transCustomer,
                        'nomor_piutang' => 'PTG-' . $this->transNoInvoice,
                        'tanggal' => $this->transTanggal,
                        'jumlah' => $this->transGrandTotal + (float) $this->transPajak,
                        'sisa' => $this->transGrandTotal + (float) $this->transPajak,
                        'status' => 'BELUM_LUNAS',
                    ]
                );
            } else {
                \App\Models\Piutang::where('transaksi_id', $this->transaksiId)->delete();
            }

            // Journal
            if ($this->transStatus === 'SELESAI') {
                \App\Services\JurnalService::buatJurnalPenjualan($transaksi);
            }

            \DB::commit();

            session()->flash('success', 'Transaksi berhasil diupdate');

            $this->redirect(route('transaksi.penjualan.show', ['id' => $this->transaksiId]), navigate: true);
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function cancelTransaksi(): void
    {
        $transaksi = Transaksi::findOrFail($this->transaksiId);

        if ($transaksi->status === 'BATAL') {
            session()->flash('error', 'Transaksi sudah dibatalkan');
            return;
        }

        try {
            \DB::transaction(function () use ($transaksi) {
                $transaksi->update(['status' => 'BATAL']);

                foreach ($transaksi->details as $detail) {
                    $barang = Barang::find($detail->barang_id);
                    if ($barang) {
                        $barang->increment('stok', $detail->qty_pcs);
                    }

                    BarangStok::updateOrCreate(
                        [
                            'barang_id' => $detail->barang_id,
                            'cabang_id' => $transaksi->cabang_id,
                        ]
                    );
                }

                if ($transaksi->metode_bayar === 'TUNAI') {
                    KasMutasi::create([
                        'cabang_id' => $transaksi->cabang_id,
                        'tanggal' => $transaksi->tanggal,
                        'tipe' => 'KELUAR',
                        'sumber' => 'REFUND',
                        'transaksi_id' => $transaksi->id,
                        'jumlah' => $transaksi->bayar,
                        'keterangan' => 'Refund pembatalan ' . $transaksi->nomor_transaksi,
                    ]);

                    if ($transaksi->kembali > 0) {
                        KasMutasi::create([
                            'cabang_id' => $transaksi->cabang_id,
                            'tanggal' => $transaksi->tanggal,
                            'tipe' => 'MASUK',
                            'sumber' => 'REFUND',
                            'transaksi_id' => $transaksi->id,
                            'jumlah' => $transaksi->kembali,
                            'keterangan' => 'Pengembalian uang ' . $transaksi->nomor_transaksi,
                        ]);
                    }
                }
            });

            \DB::commit();

            session()->flash('success', 'Transaksi berhasil dibatalkan');

            $this->redirect(route('transaksi.penjualan.list'), navigate: true);
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
