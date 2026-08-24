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
                    <input type="text" wire:model="editBarangKode" class="w-full border rounded-lg px-3 py-2">
                    @error('editBarangKode')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium">Nama Barang</label>
                    <input type="text" wire:model="editBarangNama" class="w-full border rounded-lg px-3 py-2">
                    @error('editBarangNama')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium">Stok</label>
                <input type="number" wire:model="editBarangStok" class="w-64 border rounded-lg px-3 py-2">
                @error('editBarangStok')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-6">
            <h3 class="font-semibold mb-3">Satuan Barang</h3>
            @foreach($editBarangSatuanRows as $rowIndex => $row)
                <div class="grid grid-cols-12 gap-3 mb-3">
                    <div class="col-span-4">
                        <input type="text" wire:model="editBarangSatuanRows.{{ $rowIndex }}.nama_satuan" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="col-span-3">
                        <input type="number" wire:model="editBarangSatuanRows.{{ $rowIndex }}.konversi" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="col-span-3">
                        <input type="number" wire:model="editBarangSatuanRows.{{ $rowIndex }}.harga_jual" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="col-span-2">
                        <button type="button" wire:click="removeEditBarangSatuanRow({{ $rowIndex }})" class="w-full px-3 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                    </div>
                </div>
            @endforeach
            <button type="button" wire:click="addEditBarangSatuanRow" class="px-4 py-2 bg-green-600 text-white rounded-lg">+ Tambah Satuan </button>
        </div>

        <div class="flex gap-3 mt-6">
            <a href="{{ route('master.barang.list') }}" wire:navigate class="px-4 py-2 border rounded-lg">Kembali</a>
            <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg">Update Barang</button>
        </div>
    </form>
</div>
