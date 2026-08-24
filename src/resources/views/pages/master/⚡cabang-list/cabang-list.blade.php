<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Master Cabang</h1>
            <p class="text-sm text-gray-500">Daftar cabang toko.</p>
        </div>
        <a href="{{ route('master.cabang.create') }}" wire:navigate class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Tambah Cabang</a>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-4">
                <label class="block text-sm font-medium mb-2">Cari Cabang</label>
                <input type="text" wire:model.live.debounce.300ms="searchCabangKeyword" class="w-full border rounded-lg px-3 py-2" placeholder="Kode / Nama Cabang...">
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Kode Cabang</th>
                        <th class="px-4 py-3 text-left">Nama Cabang</th>
                        <th class="px-4 py-3 text-left">Alamat</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cabangData as $index => $cabang)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $cabangData->firstItem() + $index }}</td>
                            <td class="px-4 py-3">{{ $cabang->kode_cabang }}</td>
                            <td class="px-4 py-3">{{ $cabang->nama_cabang }}</td>
                            <td class="px-4 py-3">{{ $cabang->alamat ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($cabang->is_aktif)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Aktif</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('master.cabang.edit', $cabang->id) }}" wire:navigate class="px-3 py-1 bg-amber-500 text-white rounded">Edit</a>
                                    <button type="button" onclick="confirm('Yakin hapus cabang ini?') || event.stopImmediatePropagation()" wire:click="deleteCabang({{ $cabang->id }})" class="px-3 py-1 bg-red-600 text-white rounded">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Data cabang belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $cabangData->links() }}
    </div>
</div>
