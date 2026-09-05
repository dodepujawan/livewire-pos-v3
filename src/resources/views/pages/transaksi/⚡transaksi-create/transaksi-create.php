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
    public $transPajak = 0;
    public string $transCatatan = '';

    // Cabang list
    public array $listCabang = [];

    // Cart
    public array $cartItems = [];

    // Draft
    public ?int $draftId = null;
    public bool $isDraft = false;
    public bool $showDraftBanner = false;

    // Undo delete (untuk toast BATAL)
    public array $lastDeletedItem = [];
    public bool $showUndoToast = false;
    public ?int $lastDeletedDraftId = null;
    public string $lastDeletedDraftInvoice = '';

    // Search Modal
    public bool $showSearchModal = false;
    public array $searchResults = [];
    public int $selectedIndex = 0;
    public string $searchKeyword = '';

    // Bayar Modal
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

    public function mount(): void
    {
        $this->transTanggal = now()->format('Y-m-d');
        $this->loadCabangList();
        $this->setDefaultCabang();
        $this->loadExistingDraft();
    }

    // Keyboard event handler untuk modal
    public function handleSearchModalKeydown(string $key): void
    {
        if (!$this->showSearchModal) return;

        switch ($key) {
            case 'ArrowUp':
                $this->moveSelectionUp();
                break;
            case 'ArrowDown':
                $this->moveSelectionDown();
                break;
            case 'Enter':
                if (isset($this->searchResults[$this->selectedIndex])) {
                    $this->selectBarangFromSearch($this->searchResults[$this->selectedIndex]['id']);
                }
                break;
            case 'Escape':
                $this->closeSearchModal();
                break;
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

    private function setDefaultCabang(): void
    {
        $user = Auth::user();
        if ($user && $user->cabang_id) {
            $this->transCabangId = $user->cabang_id;
        } elseif (!empty($this->listCabang)) {
            $this->transCabangId = array_key_first($this->listCabang);
        }
    }

    // ============================================================
    // DRAFT LIFECYCLE
    // ============================================================

    /**
     * Cari draft aktif milik user+cabang yang masih berlaku.
     * Draft aktif = status DRAFT + deleted_at IS NULL.
     * Jika ditemukan, muat data ke form agar kasir bisa melanjutkan.
     */
    private function loadExistingDraft(): void
    {
        $userId = Auth::id();
        if (!$userId) return;

        // Hanya ambil draft yang belum di-soft-delete
        $draft = Transaksi::where('status', 'DRAFT')
            ->whereNull('deleted_at')
            ->where('cabang_id', $this->transCabangId)
            ->where('user_id', $userId)
            ->with('details')
            ->latest()
            ->first();

        if ($draft) {
            $this->draftId = $draft->id;
            $this->isDraft = true;
            $this->showDraftBanner = true;
            $this->transNoInvoice = $draft->nomor_transaksi;
            $this->transTanggal = $draft->tanggal->format('Y-m-d');
            $this->transCustomer = $draft->customer ?? '';
            $this->transCabangId = $draft->cabang_id;
            $this->transMetodeBayar = $draft->metode_bayar;
            $this->transDiskonTotal = (float) $draft->diskon_total;
            $this->transPajak = (float) $draft->pajak;
            $this->transCatatan = $draft->catatan ?? '';
            $this->transBayar = (float) $draft->bayar;
            $this->transKembali = (float) $draft->kembali;
            $this->transGrandTotal = (float) $draft->grand_total;
            // Status draft di-load sebagai SELESAI di form bayar,
            // karena draft hanya temporary sampai bayar.
            $this->transStatus = 'SELESAI';

            // Load detail barang dari draft ke cart
            $this->cartItems = [];
            foreach ($draft->details as $detail) {
                $this->cartItems[] = [
                    'barang_id' => $detail->barang_id,
                    'barang_satuan_id' => $detail->barang_satuan_id,
                    'nama_barang' => $detail->nama_barang,
                    'nama_satuan' => $detail->nama_satuan,
                    'qty' => (int) $detail->qty,
                    'harga' => $detail->harga,
                    'diskon' => $this->formatNumber((float) $detail->diskon),
                    'subtotal' => $this->formatNumber((float) $detail->subtotal),
                    'qty_pcs' => (int) $detail->qty_pcs,
                ];
            }
        }
    }

    /**
     * Generate nomor invoice baru (TRX-YYYYMMDD-XXXX).
     * Hanya dipanggil saat membuat draft PERTAMA.
     * Setelah ada draft, nomor invoice tetap sama sampai bayar atau hapus.
     */
    private function generateInvoiceNumber(): string
    {
        $today = now()->format('Ymd');
        // Ambil transaksi terakhir (berapa nomor yang sudah terpakai)
        // Nomor invoice tetap dianggap sudah terpakai walaupun transaksinya
        // sudah di-soft-delete, karena kolom nomor_transaksi bersifat unik.
        $lastInvoice = Transaksi::withTrashed()
            ->whereDate('tanggal', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            // Ambil 4 digit terakhir, +1, pad dengan 0 di depan
            $lastNumber = (int) substr($lastInvoice->nomor_transaksi, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'TRX-' . $today . '-' . $newNumber;
    }

    /**
     * Simpan state keranjang ke tabel transaksi + transaksi_detail.
     *
     * @param bool $isFirstItem  true hanya saat barang PERTAMA kali ditambahkan.
     *                          Hanya saat $isFirstItem = true, nomor invoice baru dibuat.
     *                          Untuk barang berikutnya, nomor invoice tetap (pakai draftId).
     */
    private function saveDraft(bool $isFirstItem = false): void
    {
        $userId = Auth::id();

        // JIKA SUDAH ADA DRAFT (barang kedua, ketiga, ...)
        if ($this->draftId) {
            $transaksi = Transaksi::find($this->draftId);

            // Jika draft somehow hilang dari DB, buat draft baru
            if (!$transaksi) {
                $this->draftId = null;
                $this->isDraft = false;
                $this->saveDraft($isFirstItem);
                return;
            }

            // Update ringkasan transaksi (header saja)
            $transaksi->update([
                'customer' => $this->transCustomer,
                'grand_total' => $this->transGrandTotal,
                'diskon_total' => $this->transDiskonTotal,
                'pajak' => $this->transPajak,
                'catatan' => $this->transCatatan,
            ]);

            // Hapus detail lama, ganti dengan detail terbaru dari cart
            // (lebih aman daripada update satu-per-satu karena cart bisa berubah total)
            $transaksi->details()->delete();
            foreach ($this->cartItems as $item) {
                $transaksi->details()->create([
                    'barang_id' => $item['barang_id'],
                    'barang_satuan_id' => $item['barang_satuan_id'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'diskon' => $item['diskon'],
                    'subtotal' => $item['subtotal'],
                    'qty_pcs' => $item['qty_pcs'],
                    'harga_beli' => 0,
                    'nama_barang' => $item['nama_barang'],
                    'nama_satuan' => $item['nama_satuan'],
                ]);
            }
        }
        // JIKA BELUM ADA DRAFT (barang PERTAMA)
        else {
            // Hanya generate invoice saat barang pertama
            $invoiceNumber = $isFirstItem ? $this->generateInvoiceNumber() : '';

            // Buat transaksi DRAFT baru
            $transaksi = Transaksi::create([
                'nomor_transaksi' => $invoiceNumber,
                'tanggal' => $this->transTanggal,
                'cabang_id' => $this->transCabangId,
                'user_id' => $userId,
                'customer' => $this->transCustomer,
                'status' => 'DRAFT',
                'metode_bayar' => $this->transMetodeBayar,
                'bayar' => 0,
                'kembali' => 0,
                'grand_total' => $this->transGrandTotal,
                'diskon_total' => $this->transDiskonTotal,
                'pajak' => $this->transPajak,
                'catatan' => $this->transCatatan,
            ]);

            // Simpan id draft agar update berikutnya pakai ini
            $this->draftId = $transaksi->id;
            $this->isDraft = true;
            $this->showDraftBanner = false; // draft baru, bukan melanjutkan
            $this->transNoInvoice = $invoiceNumber;

            // Simpan setiap barang dari cart ke transaksi_detail
            foreach ($this->cartItems as $item) {
                $barang = Barang::find($item['barang_id']);
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
        }
    }

    /**
     * Soft-delete draft saat keranjang menjadi kosong.
     * Tidak menghapus row dari DB, hanya menandai deleted_at.
     * deleted_by dan delete_reategy otomatis diisi.
     */
    private function clearDraft(bool $resetUndo = true): void
    {
        if ($this->draftId) {
            $transaksi = Transaksi::find($this->draftId);
            if ($transaksi) {
                $transaksi->update([
                    'deleted_at' => now(),
                    'deleted_by' => Auth::id(),
                    'delete_reason' => 'CART_CLEARED',
                ]);
            }
        }

        $this->draftId = null;
        $this->isDraft = false;
        $this->showDraftBanner = false;
        if ($resetUndo) {
            $this->showUndoToast = false;
            $this->lastDeletedItem = [];
            $this->lastDeletedDraftId = null;
            $this->lastDeletedDraftInvoice = '';
        }
        $this->transNoInvoice = '';
        $this->transCustomer = '';
        $this->transGrandTotal = 0;
        $this->transBayar = 0;
        $this->transKembali = 0;
        $this->transDiskonTotal = 0;
        $this->transPajak = 0;
        $this->transCatatan = '';
    }

    /**
     * Tombol "Buat Draft Baru" di UI.
     * Hapus draft lama (jika ada), reset form ke kondisi awal.
     */
    public function newDraft(): void
    {
        $this->showDraftBanner = false;
        $this->clearDraft();
        $this->transTanggal = now()->format('Y-m-d');
        $this->cartItems = [];
        $this->resetItemForm();
    }

    public function searchBarang(): void
    {
        $keyword = trim($this->itemKodeBarang);

        if (empty($keyword)) {
            $this->searchBarangLike('');
            return;
        }

        $keyword = strtoupper($keyword);
        $this->searchKeyword = $keyword;

        $barang = Barang::where('kode_barang', $keyword)
            ->with('satuan')
            ->first();

        if ($barang) {
            $this->loadBarang($barang);
            $this->dispatch('focus-qty');
            return;
        }

        $this->searchBarangLike($keyword);
    }

    public function searchBarangLike(string $keyword = ''): void
    {
        $query = Barang::with('satuan')
            ->orderBy('kode_barang');

        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('kode_barang', 'LIKE', "%{$keyword}%")
                  ->orWhere('nama_barang', 'LIKE', "%{$keyword}%");
            });
        }

        $this->searchResults = $query->limit(50)->get()->map(function($barang) {
            $defaultSatuan = $barang->satuan->firstWhere('konversi', 1) ?? $barang->satuan->first();
            return [
                'id' => $barang->id,
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'stok' => $barang->stok,
                'satuan_list' => $barang->satuan->toArray(),
                'default_harga' => $defaultSatuan ? $defaultSatuan->harga_jual : 0,
                'default_satuan_id' => $defaultSatuan ? $defaultSatuan->id : 0,
                'default_satuan_nama' => $defaultSatuan ? $defaultSatuan->nama_satuan : '',
            ];
        })->toArray();

        if (count($this->searchResults) > 0) {
            $this->selectedIndex = 0;
            $this->showSearchModal = true;
        } else {
            session()->flash('error', 'Barang tidak ditemukan');
            $this->resetItemForm();
        }
    }

    public function selectBarangFromSearch(int $barangId): void
    {
        $barang = Barang::with('satuan')->find($barangId);
        if ($barang) {
            $this->loadBarang($barang);
            $this->closeSearchModal();
            $this->dispatch('focus-qty');
        }
    }

    private function loadBarang(Barang $barang): void
    {
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
    }

    public function closeSearchModal(): void
    {
        $this->showSearchModal = false;
        $this->searchResults = [];
        $this->selectedIndex = 0;
        $this->searchKeyword = '';
    }

    public function moveSelectionUp(): void
    {
        if ($this->selectedIndex > 0) {
            $this->selectedIndex--;
        }
    }

    public function moveSelectionDown(): void
    {
        if ($this->selectedIndex < count($this->searchResults) - 1) {
            $this->selectedIndex++;
        }
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

    /**
     * Format angka untuk display: hilangkan .00 jika bilangan bulat.
     * Contoh: 5000.00 → "5000", 2500.50 → "2500.5", 0 → "0"
     */
    private function formatNumber(float $value): string
    {
        if ($value == (int) $value) {
            return (string) (int) $value;
        }
        return rtrim(rtrim((string) $value, '0'), '.');
    }

    private function calculateItemSubtotal(): void
    {
        $harga = (float) $this->itemHarga;
        $qty = (int) $this->itemQty;
        $diskon = (float) $this->itemDiskon;

        $this->itemSubtotal = ($harga * $qty) - $diskon;
    }

    /**
     * Tambah barang ke keranjang, lalu SIMPAN LANGSUNG ke database via saveDraft().
     * Parameter $isFirstItem = true hanya saat ini item PERTAMA di session ini,
     * agar nomor invoice baru dibuat tepat sekali.
     */
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

        // Cek stok tersedia
        if ($barang->stok < $qtyPcs) {
            session()->flash('error', 'Stok tidak mencukupi. Stok tersedia: ' . $barang->stok . ' pcs');
            return;
        }

        // Cek apakah barang sudah ada di cart (qty dijumlahkan, tidak dobel)
        $existingIndex = null;
        foreach ($this->cartItems as $index => $item) {
            if ($item['barang_id'] === $this->itemBarangId && $item['barang_satuan_id'] === $this->itemBarangSatuanId) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Barang sudah ada — tambah qty
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
            // Barang baru — tambah ke cart
            $qty = (int) $this->itemQty;
            $diskon = $this->formatNumber((float) $this->itemDiskon);
            $subtotal = $this->formatNumber((float) $this->itemSubtotal);

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

        // Hitung ulang total dan reset form input
        $this->calculateGrandTotal();
        $this->resetItemForm();
        $this->dispatch('focus-kode-barang');

        // SIMPAN KE DB: true hanya jika ini barang PERTAMA dan belum ada draftId
        $this->saveDraft(empty($this->cartItems) === false && $this->draftId === null);
    }

    /**
     * Hapus satu item dari keranjang.
     * - Simpan item yang dihapus ke lastDeletedItem untuk undo.
     * - Tampilkan toast undo (hideUndoToast() akan dipanggil oleh JS setelah 2 detik).
     * - Jika cart masih ada item: update draft yang ada (saveDraft).
     * - Jika cart KOSONG: soft-delete draft (clearDraft).
     *   deleted_at, deleted_by, delete_reason akan terisi.
     */
    public function removeFromCart(int $index): void
    {
        if (!isset($this->cartItems[$index])) {
            return;
        }

        // Simpan item yang akan dihapus untuk undo
        $this->lastDeletedItem = $this->cartItems[$index];
        $this->lastDeletedItem['_index'] = $index;
        $this->lastDeletedDraftId = null;
        $this->lastDeletedDraftInvoice = '';

        // Hapus dari cart
        unset($this->cartItems[$index]);
        $this->cartItems = array_values($this->cartItems);
        $this->calculateGrandTotal();

        // Tampilkan toast undo
        $this->showUndoToast = true;
        $this->dispatch('undo-toast-shown');

        // Simpan ke DB
        if (empty($this->cartItems)) {
            // Draft dihapus dari daftar aktif, tetapi state undo harus tetap ada
            // agar tombol Batal dapat memulihkan draft yang sama.
            $this->lastDeletedDraftId = $this->draftId;
            $this->lastDeletedDraftInvoice = $this->transNoInvoice;
            $this->clearDraft(resetUndo: false);
        } else {
            $this->saveDraft();
        }
    }

    /**
     * Undo hapus item — kembalikan item yang baru saja dihapus.
     * Dipanggil dari tombol BATAL di toast.
     */
    public function undoRemove(): void
    {
        if (!empty($this->lastDeletedItem)) {
            $index = $this->lastDeletedItem['_index'];
            $item = $this->lastDeletedItem;

            if ($this->lastDeletedDraftId) {
                $draft = Transaksi::withTrashed()->find($this->lastDeletedDraftId);
                if ($draft) {
                    $draft->update([
                        'deleted_at' => null,
                        'deleted_by' => null,
                        'delete_reason' => null,
                    ]);
                    $this->draftId = $draft->id;
                    $this->isDraft = true;
                    $this->transNoInvoice = $this->lastDeletedDraftInvoice;
                }
            }

            // Kembalikan item ke posisi semula
            unset($item['_index']);
            array_splice($this->cartItems, $index, 0, [$item]);

            $this->calculateGrandTotal();
            $this->saveDraft();
        }

        $this->showUndoToast = false;
        $this->lastDeletedItem = [];
        $this->lastDeletedDraftId = null;
        $this->lastDeletedDraftInvoice = '';
        $this->dispatch('undo-toast-hidden');
    }

    /**
     * Tutup toast undo setelah 2 detik (dipanggil oleh JS).
     */
    public function hideUndoToast(): void
    {
        $this->showUndoToast = false;
        $this->lastDeletedItem = [];
        $this->lastDeletedDraftId = null;
        $this->lastDeletedDraftInvoice = '';
        $this->dispatch('undo-toast-hidden');
    }

    /**
     * Livewire otomatis panggil ini setiap kali user edit qty/diskons di tabel keranjang.
     * Setelah validasi, SAVE LANGSUNG ke DB via saveDraft() atau clearDraft()
     * agar data tidak hilang saat browser crash.
     */
    public function updatedCartItems(): void
    {
        foreach ($this->cartItems as $index => $item) {
            $qty = (int) $this->toFloat($item['qty'] ?? 0);
            $diskon = $this->toFloat($item['diskon'] ?? 0);
            $harga = (float) ($item['harga'] ?? 0);

            // Qty tidak boleh kurang dari 1
            if ($qty < 1) {
                $qty = 1;
                $this->cartItems[$index]['qty'] = 1;
            }

            $satuan = BarangSatuan::find($item['barang_satuan_id']);
            $qtyPcs = $satuan ? $qty * $satuan->konversi : $qty;

            // Cek stok, jika kurang batasi qty maksimal
            $barang = Barang::find($item['barang_id']);
            if ($barang && $barang->stok < $qtyPcs) {
                session()->flash('error', 'Stok tidak mencukupi untuk ' . $item['nama_barang']);
                $maxQty = floor($barang->stok / ($satuan ? $satuan->konversi : 1));
                $this->cartItems[$index]['qty'] = max(1, $maxQty);
                $qty = (int) $this->cartItems[$index]['qty'];
                $qtyPcs = $satuan ? $qty * $satuan->konversi : $qty;
            }

            $subtotal = ($harga * $qty) - $diskon;
            $this->cartItems[$index]['subtotal'] = $this->formatNumber((float) $subtotal);
            $this->cartItems[$index]['qty_pcs'] = $qtyPcs;
        }

        $this->calculateGrandTotal();

        $bayar = (float) $this->transBayar;
        $grandTotal = (float) $this->transGrandTotal;
        $this->transKembali = $bayar - $grandTotal;

        // SIMPAN KE DB: jika cart kosong → soft delete draft
        // jika cart ada → update draft yang sudah ada
        if (empty($this->cartItems)) {
            $this->clearDraft();
        } else {
            $this->saveDraft();
        }
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

    /**
     * FINALIZE: Konversi DRAFT → SELESAI / PIUTANG.
     * Ini yang dipanggil saat kasir klik "Bayar" → "Simpan".
     * Tidak membuat baris baru — update row DRAFT yang sudah ada:
     *   - Ubah status DRAFT → SELESAI (atau PIUTANG)
     *   - Kurangi stok, buat stok_mutasi, buat kas_mutasi, buat piutang, jurnal
     * Setelah ini, draftId tetap ada tapi status sudah SELESAI/PIUTANG.
     */
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

        if ($this->transStatus === 'DRAFT') {
            session()->flash('error', 'Pilih status transaksi (Selesai atau Piutang) sebelum menyimpan');
            return;
        }

        try {
            \DB::transaction(function () {
                if ($this->draftId) {
                    $transaksi = Transaksi::findOrFail($this->draftId);

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

                    $transaksi->details()->delete();

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

                        if ($barang) {
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

                    if ($this->transStatus === 'PIUTANG') {
                        \App\Models\Piutang::create([
                            'cabang_id' => $this->transCabangId,
                            'transaksi_id' => $transaksi->id,
                            'customer' => $this->transCustomer,
                            'nomor_piutang' => 'PTG-' . $this->transNoInvoice,
                            'tanggal' => $this->transTanggal,
                            'jumlah' => $this->transGrandTotal + (float) $this->transPajak,
                            'sisa' => $this->transGrandTotal + (float) $this->transPajak,
                            'status' => 'BELUM_LUNAS',
                        ]);
                    }

                    if ($this->transStatus === 'SELESAI') {
                        \App\Services\JurnalService::buatJurnalPenjualan($transaksi);
                    }
                } else {
                    $transaksi = Transaksi::create([
                        'nomor_transaksi' => $this->transNoInvoice,
                        'tanggal' => $this->transTanggal,
                        'cabang_id' => $this->transCabangId,
                        'user_id' => Auth::id(),
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

                    if ($this->transStatus === 'PIUTANG') {
                        \App\Models\Piutang::create([
                            'cabang_id' => $this->transCabangId,
                            'transaksi_id' => $transaksi->id,
                            'customer' => $this->transCustomer,
                            'nomor_piutang' => 'PTG-' . $this->transNoInvoice,
                            'tanggal' => $this->transTanggal,
                            'jumlah' => $this->transGrandTotal + (float) $this->transPajak,
                            'sisa' => $this->transGrandTotal + (float) $this->transPajak,
                            'status' => 'BELUM_LUNAS',
                        ]);
                    }

                    if ($this->transStatus === 'SELESAI') {
                        \App\Services\JurnalService::buatJurnalPenjualan($transaksi);
                    }
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

    public function openBayarModal(): void
    {
        $this->showBayarModal = true;
        $this->dispatch('focus-bayar');
    }

    public function render()
    {
        return $this->view([])
        ->layout('layouts::app')
        ->title('Buat Transaksi');
    }
};
