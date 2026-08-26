<?php

use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\BarangStok;
use App\Models\Cabang;
use App\Models\KasMutasi;
use App\Models\StokMutasi;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
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
    public string $transCatatan = '';

    // Cabang list
    public array $listCabang = [];

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

    protected $rules = [
        'transNoInvoice' => 'required|string|unique:transaksi,nomor_transaksi',
        'transTanggal' => 'required|date',
        'transCabangId' => 'required|exists:cabang,id',
        'transMetodeBayar' => 'required|in:TUNAI,TRANSFER,QRIS',
        'transBayar' => 'required|numeric|min:0',
        'itemKodeBarang' => 'nullable|string',
        'itemBarangId' => 'required|exists:barang,id',
        'itemBarangSatuanId' => 'required|exists:barang_satuan,id',
        'itemQty' => 'required|integer|min:1',
        'itemHarga' => 'required|numeric|min:0',
        'itemDiskon' => 'nullable|numeric|min:0',
    ];

    public function mount(): void
    {
        $this->transTanggal = now()->format('Y-m-d');
        $this->generateInvoiceNumber();
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
            $this->transCabangId = $user->cabang_id;
        } elseif (!empty($this->listCabang)) {
            $this->transCabangId = array_key_first($this->listCabang);
        }
    }

    private function generateInvoiceNumber(): void
    {
        $today = now()->format('Ymd');
        $lastInvoice = Transaksi::whereDate('tanggal', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->nomor_transaksi, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        $this->transNoInvoice = 'TRX-' . $today . '-' . $newNumber;
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

        if ($barang->stok < $qtyPcs) {
            session()->flash('error', 'Stok tidak mencukupi. Stok tersedia: ' . $barang->stok . ' pcs');
            return;
        }

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

    public function saveTransaksi(): void
    {
        $this->validate([
            'transNoInvoice' => 'required|string|unique:transaksi,nomor_transaksi',
            'transTanggal' => 'required|date',
            'transCabangId' => 'required|exists:cabang,id',
            'transMetodeBayar' => 'required|in:TUNAI,TRANSFER,QRIS',
            'transBayar' => 'required|numeric|min:' . $this->transGrandTotal,
        ]);

        if (empty($this->cartItems)) {
            session()->flash('error', 'Keranjang belanja kosong');
            return;
        }

        try {
            \DB::transaction(function () {
                $userId = Auth::id();

                $transaksi = Transaksi::create([
                    'nomor_transaksi' => $this->transNoInvoice,
                    'tanggal' => $this->transTanggal,
                    'cabang_id' => $this->transCabangId,
                    'user_id' => $userId,
                    'customer' => $this->transCustomer,
                    'status' => $this->transStatus,
                    'metode_bayar' => $this->transMetodeBayar,
                    'bayar' => $this->transBayar,
                    'kembali' => $this->transKembali,
                    'grand_total' => $this->transGrandTotal,
                    'diskon_total' => $this->transDiskonTotal,
                    'catatan' => $this->transCatatan,
                ]);

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

                    StokMutasi::create([
                        'barang_id' => $item['barang_id'],
                        'cabang_id' => $this->transCabangId,
                        'transaksi_id' => $transaksi->id,
                        'barang_satuan_id' => $item['barang_satuan_id'],
                        'tanggal' => $this->transTanggal,
                        'tipe' => 'KELUAR',
                        'qty' => $item['qty_pcs'],
                        'qty_satuan' => $item['qty'],
                        'keterangan' => 'Transaksi ' . $this->transNoInvoice,
                    ]);

                    if ($barang) {
                        $barangStok = BarangStok::updateOrCreate(
                            [
                                'barang_id' => $barang->id,
                                'cabang_id' => $this->transCabangId,
                            ],
                            ['stok' => $barang->stok - $item['qty_pcs']]
                        );

                        $barang->decrement('stok', $item['qty_pcs']);
                    }
                }

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

                if ($this->transStatus === 'SELESAI') {
                    \App\Services\JurnalService::buatJurnalPenjualan($transaksi);
                }
            });

            \DB::commit();

            session()->flash('success', 'Transaksi berhasil disimpan');

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
        ->title('Buat Transaksi');
    }
};
