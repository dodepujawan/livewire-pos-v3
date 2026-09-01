<div class="h-screen p-3 overflow-hidden">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-lg font-bold">Buat Transaksi</h1>
        </div>
        <a href="{{ route('transaksi.penjualan.list') }}" wire:navigate class="px-3 py-1.5 border rounded hover:bg-gray-50 text-sm mr-1 mt-1">Kembali</a>
    </div>

    @if (session('success'))
        <div class="mb-2 p-2 rounded bg-green-100 text-green-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-2 p-2 rounded bg-red-100 text-red-800 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="saveTransaksi" class="h-[calc(100vh-60px)]">
        {{-- Main Grid Layout --}}
        <div class="grid gap-2 h-full min-h-0" style="grid-template-columns: 300px minmax(0,1fr) 300px;">

            {{-- Left Column: Add Item only (fixed 300px) --}}
            <div class="flex flex-col gap-2 min-h-0 overflow-y-auto pr-1">
                {{-- Add Item Section --}}
                <div class="bg-white rounded shadow p-3 shrink-0"
                     x-data="{ navFlow: ['qty-input','satuan-input','diskon-input','tambah-button'],
                              handleNav(event) {
                                  if (event.key !== 'Enter' || @this.get('showSearchModal') || @this.get('showBayarModal')) return;
                                  event.preventDefault();
                                  this.navNext(event.target.id ?? '');
                              },
                              navNext(id) {
                                  const idx = this.navFlow.indexOf(id);
                                  if (idx === -1) return;
                                  if (id === 'tambah-button') {
                                     $wire.addToCart();
                                     return;
                                  }
                                  document.getElementById(this.navFlow[idx + 1]).focus();
                              } }"
                     x-on:keydown="handleNav">
                    <h3 class="font-semibold mb-2 text-sm">Tambah Barang</h3>
                    <div class="space-y-2">
                        {{-- Kode Barang --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Kode Barang</label>
                            <input
                                type="text"
                                wire:model.live="itemKodeBarang"
                                wire:keydown.enter.prevent="searchBarang"
                                id="kode-barang-input"
                                class="w-full border rounded px-3 py-1.5 text-sm"
                                placeholder="Scan/ketik kode (kosong + Enter untuk semua barang)..."
                            >
                            @error('itemKodeBarang')
                                <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Found Barang Details --}}
                        @if($itemNamaBarang)
                            <div class="text-sm bg-blue-50 p-1.5 rounded">
                                <strong>{{ $itemNamaBarang }}</strong>
                                <span class="ml-2 text-gray-500">Stok: {{ $itemStok }}</span>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            {{-- Qty --}}
                            <div>
                                <label class="block text-sm font-medium mb-1">Qty</label>
                                <input
                                    type="number"
                                    wire:model.live.debounce.500ms="itemQty"
                                    id="qty-input"
                                    min="1"
                                    class="w-full border rounded px-3 py-1.5 text-sm"
                                    @if(!$itemNamaBarang) disabled @endif
                                >
                                @error('itemQty')
                                    <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Satuan --}}
                            <div>
                                <label class="block text-sm font-medium mb-1">Satuan</label>
                                <select wire:model.live.debounce.500ms="itemBarangSatuanId" id="satuan-input" class="w-full border rounded px-3 py-1.5 text-sm" @disabled(empty($itemSatuanList))>
                                    <option value="0">Pilih</option>
                                    @foreach($itemSatuanList as $satuan)
                                        <option value="{{ $satuan['id'] }}">{{ $satuan['nama_satuan'] }}</option>
                                    @endforeach
                                </select>
                                @error('itemBarangSatuanId')
                                    <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Harga --}}
                            <div>
                                <label class="block text-sm font-medium mb-1">Harga</label>
                                <input type="text" value="{{ number_format($itemHarga) }}" readonly class="w-full border rounded px-3 py-1.5 bg-gray-100 text-sm">
                            </div>

                            {{-- Diskon --}}
                            <div>
                                <label class="block text-sm font-medium mb-1">Diskon</label>
                                <input type="number" wire:model.live.debounce.500ms="itemDiskon" id="diskon-input" min="0" class="w-full border rounded px-3 py-1.5 text-sm">
                                @error('itemDiskon')
                                    <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Subtotal --}}
                            <div class="col-span-2">
                                <label class="block text-sm font-medium mb-1">Subtotal</label>
                                <input type="text" readonly class="w-full border rounded px-3 py-1.5 bg-gray-100 text-sm" value="{{ number_format($itemSubtotal, 0, ',', '.') }}">
                            </div>
                        </div>

                        {{-- Button Tambah --}}
                        <button type="button" @click="$wire.addToCart()" id="tambah-button" class="w-full px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm" @if(!$itemNamaBarang) disabled @endif>+ Tambah ke Keranjang</button>
                    </div>
                </div>
            </div>

            {{-- Middle Column: Cart (flexible width) --}}
            <div class="bg-white rounded shadow p-2 min-h-0 overflow-hidden flex flex-col">
                <h3 class="font-semibold mb-2 text-xs shrink-0">Keranjang Belanja</h3>

                @if(count($cartItems) > 0)
                    <div class="max-h-[60vh] overflow-y-auto">
                        <table class="w-full text-xs" style="table-layout: fixed;">
                            <colgroup>
                                <col style="width: 28px;">
                                <col style="width: auto;">
                                <col style="width: 70px;">
                                <col style="width: 60px;">
                                <col style="width: 80px;">
                                <col style="width: 70px;">
                                <col style="width: 80px;">
                                <col style="width: 60px;">
                            </colgroup>
                            <thead class="bg-gray-100 sticky top-0">
                                <tr>
                                    <th class="px-2 py-1.5 text-left text-xs">No</th>
                                    <th class="px-2 py-1.5 text-left text-xs">Barang</th>
                                    <th class="px-2 py-1.5 text-left text-xs">Satuan</th>
                                    <th class="px-2 py-1.5 text-right text-xs">Qty</th>
                                    <th class="px-2 py-1.5 text-right text-xs">Harga</th>
                                    <th class="px-2 py-1.5 text-right text-xs">Diskon</th>
                                    <th class="px-2 py-1.5 text-right text-xs">Subtotal</th>
                                    <th class="px-2 py-1.5 text-center text-xs">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($cartItems as $index => $item)
                                    <tr>
                                        <td class="px-2 py-1.5 text-xs">{{ $index + 1 }}</td>
                                        <td class="px-2 py-1.5 text-xs">{{ $item['nama_barang'] }}</td>
                                        <td class="px-2 py-1.5 text-xs">{{ $item['nama_satuan'] }}</td>
                                        <td class="px-2 py-1.5 text-right text-xs">
                                            <input
                                                type="number"
                                                wire:model.live.debounce.300ms="cartItems.{{ $index }}.qty"
                                                min="1"
                                                class="w-full text-right border rounded px-1 py-0.5 text-xs"
                                            >
                                        </td>
                                        <td class="px-2 py-1.5 text-right text-xs">{{ number_format($item['harga'], 0, ',', '.') }}</td>
                                        <td class="px-2 py-1.5 text-right text-xs">
                                            <input
                                                type="number"
                                                wire:model.live.debounce.300ms="cartItems.{{ $index }}.diskon"
                                                min="0"
                                                class="w-full text-right border rounded px-1 py-0.5 text-xs"
                                            >
                                        </td>
                                        <td class="px-2 py-1.5 text-right text-xs">{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                        <td class="px-2 py-1.5 text-center">
                                            <button type="button" wire:click="removeFromCart({{ $index }})" class="px-1.5 py-0.5 bg-red-500 text-white rounded hover:bg-red-600 text-xs">Hapus</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex items-center justify-center text-gray-500 text-xs py-8">
                        Keranjang belanja kosong
                    </div>
                @endif
            </div>

            {{-- Right Column: Header + Payment (fixed 300px) --}}
            <div class="flex flex-col gap-2 min-h-0 overflow-y-auto pl-1">
                {{-- Header Section (moved from left) --}}
                <div class="bg-white rounded shadow p-3 shrink-0">
                    <div class="space-y-2">
                        {{-- Nomor Invoice --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">No. Invoice</label>
                            <input type="text" wire:model="transNoInvoice" readonly class="w-full border rounded px-3 py-1.5 bg-gray-100 text-sm">
                            @error('transNoInvoice')
                                <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal</label>
                            <input type="date" wire:model="transTanggal" class="w-full border rounded px-3 py-1.5 text-sm">
                            @error('transTanggal')
                                <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Customer --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Customer</label>
                            <input type="text" wire:model="transCustomer" class="w-full border rounded px-3 py-1.5 text-sm" placeholder="Opsional">
                            @error('transCustomer')
                                <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Cabang --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Cabang</label>
                            <select wire:model="transCabangId" class="w-full border rounded px-3 py-1.5 text-sm">
                                <option value="0">Pilih Cabang</option>
                                @foreach($listCabang as $id => $nama)
                                    <option value="{{ $id }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                            @error('transCabangId')
                                <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Metode Bayar --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Metode Bayar</label>
                            <select wire:model="transMetodeBayar" class="w-full border rounded px-3 py-1.5 text-sm">
                                <option value="TUNAI">Tunai</option>
                                <option value="TRANSFER">Transfer</option>
                                <option value="QRIS">QRIS</option>
                            </select>
                            @error('transMetodeBayar')
                                <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Grand Total Display --}}
                <div class="bg-white rounded shadow p-2 border-t-2 border-blue-200 bg-gradient-to-r from-blue-50 to-blue-100 shrink-0">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold text-blue-800">GRAND TOTAL</span>
                        <span class="text-xl font-black text-blue-600">{{ number_format($transGrandTotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col gap-2 shrink-0">
                    <button type="button" wire:click="openBayarModal" class="w-full px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold text-xs">Bayar</button>
                    <a href="{{ route('transaksi.penjualan.list') }}" wire:navigate class="w-full px-3 py-1.5 border rounded hover:bg-gray-50 text-center text-xs">Kembali</a>
                </div>
            </div>
        </div>
    </form>

    {{-- Search Modal --}}
    @if($showSearchModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50"
         x-data="{
             handleKeydown(event) {
                 if (event.key === 'ArrowUp' || event.key === 'ArrowDown' || event.key === 'Enter' || event.key === 'Escape') {
                     event.preventDefault();
                     @this.handleSearchModalKeydown(event.key);
                 }
             }
         }"
         x-on:keydown.window="handleKeydown"
         wire:keydown.escape="$set('showSearchModal', false)"
         wire:click.self="$set('showSearchModal', false)">
        <div class="w-full max-w-4xl mx-4 max-h-[80vh] overflow-hidden rounded-xl bg-white shadow-2xl flex flex-col">
            <div class="p-4 border-b bg-gray-50">
                <div class="flex justify-between items-center">
                    <h2 class="text-base font-bold text-gray-800">Pilih Barang</h2>
                    <button type="button" @click="$wire.set('showSearchModal', false)" class="px-3 py-1 text-sm border rounded hover:bg-gray-100">
                        ESC (Tutup)
                    </button>
                </div>
                <div class="mt-2">
                    <div class="flex items-center gap-2">
                        <input type="text"
                               wire:model.live.debounce.300ms="searchKeyword"
                               class="flex-1 border rounded px-3 py-1.5 text-sm"
                               placeholder="Ketik untuk filter..."
                               autofocus>
                        <span class="text-sm text-gray-500">
                            {{ count($searchResults) }} hasil
                        </span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        Gunakan ↑ ↓ untuk navigasi, Enter untuk pilih, ESC untuk tutup
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left">Kode</th>
                            <th class="px-4 py-2 text-left">Nama Barang</th>
                            <th class="px-4 py-2 text-right">Stok</th>
                            <th class="px-4 py-2 text-right">Harga</th>
                            <th class="px-4 py-2 text-left">Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($searchResults as $index => $result)
                            <tr wire:key="search-result-{{ $result['id'] }}"
                                class="cursor-pointer transition-all duration-150 {{ $selectedIndex === $index ? 'bg-blue-50 border-l-4 border-blue-500 shadow-sm' : 'hover:bg-gray-50' }}"
                                x-on:dblclick="$wire.selectBarangFromSearch({{ $result['id'] }})"
                                x-on:click="$wire.set('selectedIndex', {{ $index }})"
                                :class="{ 'bg-blue-50 border-l-4 border-blue-500 shadow-sm': {{ $selectedIndex }} === {{ $index }} }">
                                <td class="px-4 py-2 font-mono text-sm">{{ $result['kode_barang'] }}</td>
                                <td class="px-4 py-2">{{ $result['nama_barang'] }}</td>
                                <td class="px-4 py-2 text-right">{{ $result['stok'] }}</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($result['default_harga'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ $result['default_satuan_nama'] }}</td>
                            </tr>
                        @endforeach
                        @if(empty($searchResults) || count($searchResults) === 0)
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    Tidak ada barang ditemukan
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t bg-gray-50">
                <div class="flex justify-between items-center text-sm">
                    <div class="text-gray-600">
                        @if(isset($searchResults[$selectedIndex]))
                            Terpilih: <span class="font-semibold">{{ $searchResults[$selectedIndex]['nama_barang'] }}</span>
                        @else
                            Pilih barang dengan navigasi keyboard
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <button type="button"
                                @click="$wire.set('showSearchModal', false)"
                                class="px-4 py-1.5 border rounded hover:bg-gray-100 text-sm">
                            Batal
                        </button>
                        @if(isset($searchResults[$selectedIndex]))
                            <button type="button"
                                    @click="$wire.selectBarangFromSearch({{ $searchResults[$selectedIndex]['id'] }})"
                                    class="px-4 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
                                Pilih Barang (Enter)
                            </button>
                        @endif
                    </div>
</div>
            </div>
    </div>
    @endif

    {{-- Bayar Modal --}}
    @if($showBayarModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('showBayarModal', false)">
        <div class="w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-xl">
            <div class="p-4">
                <h2 class="text-base font-bold mb-3">Konfirmasi Pembayaran</h2>

                {{-- Payment Card --}}
                <div class="space-y-2 mb-3">
                    <div>
                        <label class="block text-xs font-medium mb-1">Bayar</label>
                        <input type="number" wire:model.live="transBayar" min="0" class="w-full border rounded px-2 py-1 text-xs">
                        @error('transBayar')<p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Kembali</label>
                        <input type="text" readonly class="w-full border rounded px-2 py-1 bg-gray-100 text-xs" value="{{ number_format($transKembali, 0, ',', '.') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Diskon Total</label>
                        <input type="number" wire:model="transDiskonTotal" min="0" class="w-full border rounded px-2 py-1 text-xs">
                        @error('transDiskonTotal')<p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Pajak (PPN)</label>
                        <input type="number" wire:model="transPajak" min="0" class="w-full border rounded px-2 py-1 text-xs">
                        @error('transPajak')<p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Status</label>
                        <select wire:model="transStatus" class="w-full border rounded px-2 py-1 text-xs">
                            <option value="SELESAI">Selesai (Tunai/Lunas)</option>
                            <option value="PIUTANG">Piutang (Belum Bayar)</option>
                        </select>
                        @error('transStatus')<p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan</label>
                        <textarea wire:model="transCatatan" rows="2" class="w-full border rounded px-2 py-1 text-xs"></textarea>
                        @error('transCatatan')<p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Grand Total in Modal --}}
                <div class="p-2 border-t-2 border-blue-200 bg-gradient-to-r from-blue-50 to-blue-100 rounded mb-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold text-blue-800">GRAND TOTAL</span>
                        <span class="text-lg font-black text-blue-600">{{ number_format($transGrandTotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Modal Buttons --}}
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('showBayarModal', false)" class="flex-1 px-3 py-2 border rounded hover:bg-gray-50 text-xs">Kembali</button>
                    <button type="submit" wire:click="saveTransaksi" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold text-xs">Simpan</button>
                </div>
</div>
            </div>
        </div>
    </div>
    @endif
</div>

@script
<script>
    $wire.on('focus-qty', () => {
        setTimeout(() => {
            document.getElementById('qty-input')?.focus();
        }, 50);
    });

    $wire.on('focus-kode-barang', () => {
        setTimeout(() => {
            document.getElementById('kode-barang-input')?.focus();
        }, 50);
    });
</script>
@endscript
