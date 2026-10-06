<div class="max-w-7xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Edit Barang</h1>
        <p class="text-gray-500 text-sm">Ubah data master barang.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-green-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-800">{{ session('error') }}</div>
    @endif

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
                    <label class="block mb-2 text-sm font-medium">Cabang untuk Penyesuaian Stok</label>
                    <select wire:model.live="editBarangCabangId" class="w-full border rounded-lg px-3 py-2">
                        <option value="0">Pilih cabang</option>
                        @foreach($listCabang as $id => $nama)
                            <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                    @error('editBarangCabangId')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium">Jumlah Stok Cabang</label>
                    <input type="number" wire:model="editBarangStok" min="0" class="w-full border rounded-lg px-3 py-2">
                    @error('editBarangStok')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Isi jumlah akhir. Jika berubah, riwayat stok akan mencatat selisihnya.</p>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium">Alasan Perubahan Stok</label>
                    <input type="text" wire:model="editBarangStokAlasan" maxlength="255" class="w-full border rounded-lg px-3 py-2" placeholder="Contoh: hasil stok opname">
                    @error('editBarangStokAlasan')
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

        @if(count($listCabang) > 1)
            <div class="mt-6 rounded-xl border bg-white p-6 shadow">
                <h3 class="font-semibold">Transfer Stok Antar Cabang</h3>
                <p class="mt-1 text-sm text-gray-500">Jumlah stok perusahaan tidak berubah; stok hanya berpindah lokasi.</p>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Dari Cabang</label>
                        <select wire:model="transferStokDariCabangId" class="w-full rounded-lg border px-3 py-2">
                            @foreach($listCabang as $id => $nama)
                                <option value="{{ $id }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('transferStokDariCabangId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Ke Cabang</label>
                        <select wire:model="transferStokKeCabangId" class="w-full rounded-lg border px-3 py-2">
                            <option value="0">Pilih cabang tujuan</option>
                            @foreach($listCabang as $id => $nama)
                                <option value="{{ $id }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('transferStokKeCabangId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Jumlah (PCS)</label>
                        <input type="number" wire:model="transferStokQty" min="1" class="w-full rounded-lg border px-3 py-2">
                        @error('transferStokQty')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Alasan</label>
                        <input type="text" wire:model="transferStokAlasan" maxlength="150" class="w-full rounded-lg border px-3 py-2" placeholder="Contoh: pengisian stok cabang">
                        @error('transferStokAlasan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <button type="button"
                        wire:click="pindahkanStok"
                        wire:loading.attr="disabled"
                        wire:target="pindahkanStok"
                        class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50">
                    Pindahkan Stok
                </button>
            </div>
        @endif

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
