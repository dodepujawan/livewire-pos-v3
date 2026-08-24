<div class="max-w-7xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Tambah Cabang</h1>
        <p class="text-gray-500 text-sm">Tambahkan master cabang baru.</p>
    </div>

    <form wire:submit="saveCabang">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 text-sm font-medium">Kode Cabang</label>
                    <input type="text" wire:model="createCabangKode" class="w-full border rounded-lg px-3 py-2">
                    @error('createCabangKode')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium">Nama Cabang</label>
                    <input type="text" wire:model="createCabangNama" class="w-full border rounded-lg px-3 py-2">
                    @error('createCabangNama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label class="block mb-2 text-sm font-medium">Alamat</label>
                <textarea wire:model="createCabangAlamat" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Opsional"></textarea>
                @error('createCabangAlamat')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="createCabangAktif" class="rounded border-gray-300">
                    <span class="text-sm font-medium">Aktif</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <a href="{{ route('master.cabang.list') }}" wire:navigate class="px-4 py-2 border rounded-lg">Kembali</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan Cabang</button>
        </div>
    </form>
</div>
