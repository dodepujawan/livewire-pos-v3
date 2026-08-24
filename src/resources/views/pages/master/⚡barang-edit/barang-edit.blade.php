<div class="max-w-7xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Edit Barang</h1>
        <p class="text-gray-500 text-sm">Ubah data master barang.</p>
    </div>

    <form wire:submit="updateBarang">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 text-sm font-medium">Kode Barang</label>
                    <input type="text" wire:model="editBarangKode" x-on:input="$el.value = $el.value.toUpperCase()" class="w-full border rounded-lg px-3 py-2">
                    @error('editBarangKode')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium">Nama Barang</label>
                    <input type="text" wire:model="editBarangNama" x-on:input="$el.value = $el.value.toUpperCase()" class="w-full border rounded-lg px-3 py-2">
                    @error('editBarangNama')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block mb-2 text-sm font-medium">Stok</label>
                    <input type="number" wire:model="editBarangStok" class="w-full md:w-64 border rounded-lg px-3 py-2">
                    @error('editBarangStok')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium">Harga Beli (PCS)</label>
                    <input type="number" wire:model="editBarangHargaBeli" class="w-full md:w-64 border rounded-lg px-3 py-2" min="0">
                    @error('editBarangHargaBeli')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="font-semibold mb-3">Satuan Barang</h3>
            @foreach($editBarangSatuanRows as $rowIndex => $row)
                <div class="grid grid-cols-12 gap-3 mb-3 items-end">
                    <div class="col-span-3">
                        <label class="block text-xs mb-1">Nama Satuan</label>
                        <input type="text" wire:model="editBarangSatuanRows.{{ $rowIndex }}.nama_satuan" x-on:input="$el.value = $el.value.toUpperCase()" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs mb-1">Konversi</label>
                        <input type="number" wire:model="editBarangSatuanRows.{{ $rowIndex }}.konversi" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs mb-1">Harga Jual</label>
                        <input type="number" wire:model="editBarangSatuanRows.{{ $rowIndex }}.harga_jual" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs mb-1">Harga Beli</label>
                        <input type="number" wire:model="editBarangSatuanRows.{{ $rowIndex }}.harga_beli" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs mb-1">Default</label>
                        <label class="flex items-center justify-center h-[42px] cursor-pointer">
                            <input type="radio" name="edit_default_satuan" value="{{ $rowIndex }}" wire:model.live="editDefaultSatuanIndex" class="border-gray-300">
                        </label>
                    </div>
                    <div class="col-span-1">
                        <button type="button" wire:click="removeEditBarangSatuanRow({{ $rowIndex }})" class="w-full px-3 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                    </div>
                </div>
            @endforeach
            <button type="button" wire:click="addEditBarangSatuanRow" class="px-4 py-2 bg-green-600 text-white rounded-lg">+ Tambah Satuan</button>
        </div>

        <div class="flex gap-3 mt-6">
            <a href="{{ route('master.barang.list') }}" wire:navigate class="px-4 py-2 border rounded-lg">Kembali</a>
            <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg">Update Barang</button>
        </div>
    </form>
</div>
