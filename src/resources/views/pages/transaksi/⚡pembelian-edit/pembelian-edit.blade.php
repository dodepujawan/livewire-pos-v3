<div class="h-screen p-3 overflow-hidden">
    <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-lg font-bold">Edit Pembelian</h1>
        </div>
        <a href="{{ route('transaksi.pembelian.list') }}" wire:navigate class="px-3 py-1.5 border rounded hover:bg-gray-50 text-sm mr-1 mt-1">Kembali</a>
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

    <form wire:submit.prevent="updatePembelian" class="h-[calc(100vh-60px)]">
        <div class="grid gap-2 h-full min-h-0" style="grid-template-columns: 300px minmax(0,1fr) 300px;">

            <div class="flex flex-col gap-2 min-h-0 overflow-y-auto pr-1">
                <div class="bg-white rounded shadow p-3 shrink-0">
                    <div class="space-y-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nomor Pembelian</label>
                            <input type="text" wire:model="pembelianNo" @if($pembelianStatus !== 'ORDER') readonly @endif class="w-full border rounded px-3 py-1.5 text-sm {{ $pembelianStatus !== 'ORDER' ? 'bg-gray-100' : '' }}">
                            @error('pembelianNo')
                                <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal</label>
                            <input type="date" wire:model="pembelianTanggal" @if($pembelianStatus !== 'ORDER') readonly @endif class="w-full border rounded px-3 py-1.5 text-sm {{ $pembelianStatus !== 'ORDER' ? 'bg-gray-100' : '' }}">
                            @error('pembelianTanggal')
                                <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Cabang</label>
                            <select wire:model="pembelianCabangId" @if($pembelianStatus !== 'ORDER') disabled @endif class="w-full border rounded px-3 py-1.5 text-sm {{ $pembelianStatus !== 'ORDER' ? 'bg-gray-100' : '' }}">
                                @foreach($listCabang as $id => $nama)
                                    <option value="{{ $id }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                            @error('pembelianCabangId')
                                <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Supplier</label>
                            <input type="text" wire:model="pembelianSupplier" @if($pembelianStatus !== 'ORDER') readonly @endif class="w-full border rounded px-3 py-1.5 text-sm {{ $pembelianStatus !== 'ORDER' ? 'bg-gray-100' : '' }}">
                            @error('pembelianSupplier')
                                <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <input type="text" value="{{ $pembelianStatus }}" readonly class="w-full border rounded px-3 py-1.5 bg-gray-100 text-sm">
                        </div>
                    </div>
                </div>

                @if($pembelianStatus === 'ORDER')
                    <div class="bg-white rounded shadow p-3 shrink-0">
                        <h3 class="font-semibold mb-2 text-sm">Tambah Barang</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-sm font-medium mb-1">Kode Barang</label>
                                <input
                                    type="text"
                                    wire:model.live="itemKodeBarang"
                                    wire:keydown.enter.prevent="searchBarang"
                                    id="kode-barang-input"
                                    class="w-full border rounded px-3 py-1.5 text-sm"
                                    placeholder="Scan/ketik kode..."
                                >
                                @error('itemKodeBarang')
                                    <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($itemNamaBarang)
                                <div class="text-sm bg-blue-50 p-1.5 rounded">
                                    <strong>{{ $itemNamaBarang }}</strong>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Qty</label>
                                    <input
                                        type="number"
                                        wire:model.live.debounce.500ms="itemQty"
                                        wire:keydown.enter.prevent="addToCart"
                                        id="qty-input"
                                        min="0.01"
                                        step="0.01"
                                        class="w-full border rounded px-3 py-1.5 text-sm"
                                        @if(!$itemNamaBarang) disabled @endif
                                    >
                                    @error('itemQty')
                                        <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Satuan</label>
                                    <select wire:model.live="itemBarangSatuanId" class="w-full border rounded px-3 py-1.5 text-sm" @disabled(empty($itemSatuanList))>
                                        <option value="0">Pilih</option>
                                        @foreach($itemSatuanList as $satuan)
                                            <option value="{{ $satuan['id'] }}">{{ $satuan['nama_satuan'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('itemBarangSatuanId')
                                        <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-sm font-medium mb-1">Harga Beli</label>
                                    <input type="number" wire:model.live.debounce.500ms="itemHargaBeli" min="0" class="w-full border rounded px-3 py-1.5 text-sm">
                                    @error('itemHargaBeli')
                                        <p class="text-red-500 text-sm mt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-sm font-medium mb-1">Subtotal</label>
                                    <input type="text" readonly class="w-full border rounded px-3 py-1.5 bg-gray-100 text-sm" value="{{ number_format($itemSubtotal, 0, ',', '.') }}">
                                </div>
                            </div>

                            <button type="button" wire:click="addToCart" class="w-full px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm" @if(!$itemNamaBarang) disabled @endif>+ Tambah ke Pembelian</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded shadow p-2 min-h-0 overflow-hidden flex flex-col">
                <h3 class="font-semibold mb-2 text-xs shrink-0">Keranjang Pembelian</h3>

                @if(count($cartItems) > 0)
                    <div class="max-h-[60vh] overflow-y-auto">
                        <table class="w-full text-xs" style="table-layout: fixed;">
                            <colgroup>
                                <col style="width: 28px;">
                                <col style="width: auto;">
                                <col style="width: 80px;">
                                <col style="width: 90px;">
                                <col style="width: 100px;">
                                <col style="width: 60px;">
                            </colgroup>
                            <thead class="bg-gray-100 sticky top-0">
                                <tr>
                                    <th class="px-2 py-1.5 text-left text-xs">No</th>
                                    <th class="px-2 py-1.5 text-left text-xs">Barang</th>
                                    <th class="px-2 py-1.5 text-right text-xs">Qty</th>
                                    <th class="px-2 py-1.5 text-right text-xs">Harga Beli</th>
                                    <th class="px-2 py-1.5 text-right text-xs">Subtotal</th>
                                    <th class="px-2 py-1.5 text-center text-xs">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($cartItems as $index => $item)
                                    <tr>
                                        <td class="px-2 py-1.5 text-xs">{{ $index + 1 }}</td>
                                        <td class="px-2 py-1.5 text-xs">{{ $item['nama_barang'] }}</td>
                                        <td class="px-2 py-1.5 text-right text-xs">
                                            <input
                                                type="number"
                                                wire:model.live.debounce.300ms="cartItems.{{ $index }}.qty"
                                                min="0.01"
                                                step="0.01"
                                                class="w-full text-right border rounded px-2 py-1 text-xs"
                                                @if($pembelianStatus !== 'ORDER') disabled @endif
                                            >
                                        </td>
                                        <td class="px-2 py-1.5 text-right text-xs">
                                            <input
                                                type="number"
                                                wire:model.live.debounce.300ms="cartItems.{{ $index }}.harga_beli"
                                                min="0"
                                                class="w-full text-right border rounded px-2 py-1 text-xs"
                                                @if($pembelianStatus !== 'ORDER') disabled @endif
                                            >
                                        </td>
                                        <td class="px-2 py-1.5 text-right text-xs">{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                        <td class="px-2 py-1.5 text-center">
                                            @if($pembelianStatus === 'ORDER')
                                                <button type="button" wire:click="removeFromCart({{ $index }})" class="px-1.5 py-0.5 bg-red-500 text-white rounded hover:bg-red-600 text-xs">Hapus</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex items-center justify-center text-gray-500 text-xs py-8">
                        Keranjang pembelian kosong
                    </div>
                @endif
            </div>

            <div class="flex flex-col gap-2 min-h-0 overflow-y-auto pl-1">
                <div class="bg-white rounded shadow p-2 shrink-0">
                    <h3 class="font-semibold mb-2 text-xs">Total Pembelian</h3>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold text-gray-700">TOTAL</span>
                        <span class="text-xl font-black text-blue-600">{{ number_format($pembelianTotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Pajak --}}
                <div class="bg-white rounded shadow p-2 shrink-0">
                    <div class="flex justify-between items-center">
                        <label class="text-xs font-semibold text-gray-700">Pajak (PPN)</label>
                        <input type="number" wire:model="pembelianPajak" min="0" class="w-32 border rounded px-2 py-1 text-xs text-right" @if($pembelianStatus !== 'ORDER') disabled @endif>
                    </div>
                </div>

                <div class="flex flex-col gap-2 shrink-0">
                    @if($pembelianStatus === 'ORDER')
                        <button type="submit" class="w-full px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold text-xs">Simpan Perubahan</button>
                        <button type="button" wire:click="receivePembelian" class="w-full px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold text-xs">Terima Pembelian</button>
                        <button type="button" wire:click="cancelPembelian" class="w-full px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-semibold text-xs">Batalkan</button>
                    @endif
                    <a href="{{ route('transaksi.pembelian.list') }}" wire:navigate class="w-full px-3 py-1.5 border rounded hover:bg-gray-50 text-center text-xs">Kembali</a>
                </div>
            </div>
        </div>
    </form>
</div>
@script
<script>
document.addEventListener('livewire:init', () => {
    document.getElementById('kode-barang-input').focus();

    @this.on('focus-qty', () => {
        document.getElementById('qty-input').focus();
    });

    @this.on('focus-kode-barang', () => {
        document.getElementById('kode-barang-input').focus();
    });
});
</script>
@endscript
