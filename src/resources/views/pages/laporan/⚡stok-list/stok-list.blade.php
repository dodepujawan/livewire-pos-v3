<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Laporan Mutasi Stok</h1>
            <p class="text-sm text-gray-500">Catatan masuk & keluar stok per barang.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Total Stok Masuk</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalMasuk, 0, ',', '.') }} pcs</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Total Stok Keluar</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($totalKeluar, 0, ',', '.') }} pcs</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-12 md:col-span-4">
                <label class="block text-sm font-medium mb-2">Cari Barang/Kode</label>
                <input type="text" wire:model.live.debounce.300ms="searchKeyword" placeholder="Nama atau kode barang..." class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-6 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Cabang</label>
                <select wire:model.live="cabangId" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Semua Cabang</option>
                    @foreach($listCabang as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Tanggal Dari</label>
                <input type="date" wire:model.live="dateFrom" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Tanggal Sampai</label>
                <input type="date" wire:model.live="dateTo" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-12 md:col-span-1">
                <button wire:click="resetFilter" class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Reset</button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Barang</th>
                        <th class="px-4 py-3 text-left">Cabang</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-right">Qty (Pcs)</th>
                        <th class="px-4 py-3 text-right">Qty (Satuan)</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasis as $mutasi)
                        <tr class="border-t {{ $mutasi->tipe === 'MASUK' ? 'bg-green-50' : 'bg-red-50' }}">
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $mutasi->barang->nama_barang ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $mutasi->barang->kode_barang ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $mutasi->cabang->nama_cabang ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $mutasi->tipe === 'MASUK' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $mutasi->tipe }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right {{ $mutasi->tipe === 'MASUK' ? 'text-green-700' : 'text-red-700' }}">
                                {{ number_format($mutasi->qty, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format($mutasi->qty_satuan ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-xs">{{ $mutasi->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">Data mutasi stok belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $mutasis->links() }}</div>
</div>
