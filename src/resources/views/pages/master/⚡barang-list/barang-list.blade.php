<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Master Barang</h1>
            <p class="text-sm text-gray-500">Daftar barang dan stok.</p>
        </div>
        <a href="{{ route('master.barang.create') }}" wire:navigate class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Tambah Barang</a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-4">
                <label class="block text-sm font-medium mb-2">Cari Barang</label>
                <input type="text" wire:model.live.debounce.300ms="searchBarangKeyword" class="w-full border rounded-lg px-3 py-2" placeholder="Kode / Nama Barang...">
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Kode Barang</th>
                        <th class="px-4 py-3 text-left">Nama Barang</th>
                        <th class="px-4 py-3 text-left">Satuan</th>
                        <th class="px-4 py-3 text-right">Stok</th>
                        <th class="px-4 py-3 text-center w-40">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangData as $index => $barang)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $barangData->firstItem() + $index }}</td>
                            <td class="px-4 py-3">{{ $barang->kode_barang }}</td>
                            <td class="px-4 py-3">{{ $barang->nama_barang }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($barang->satuan as $satuan)
                                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                            {{ $satuan->nama_satuan }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format($barang->stok) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('barang-edit', $barang->id) }}" wire:navigate class="px-3 py-1 bg-amber-500 text-white rounded">Edit</a>
                                    <button type="button" onclick="confirm('Yakin hapus barang ini?') || event.stopImmediatePropagation()" wire:click="deleteBarang({{ $barang->id }})"class="px-3 py-1 bg-red-600 text-white rounded">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">Data barang belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $barangData->links() }}
    </div>
</div>
