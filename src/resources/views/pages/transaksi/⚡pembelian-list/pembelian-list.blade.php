<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Pembelian Barang</h1>
            <p class="text-sm text-gray-500">Daftar pembelian dari supplier.</p>
        </div>
        <a href="{{ route('transaksi.pembelian.create') }}" wire:navigate class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Buat Pembelian</a>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-12 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Cari</label>
                <input type="text" wire:model.live.debounce.300ms="searchKeyword" class="w-full border rounded-lg px-3 py-2" placeholder="Nomor / Supplier...">
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Tanggal Dari</label>
                <input type="date" wire:model.live="dateFrom" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Tanggal Sampai</label>
                <input type="date" wire:model.live="dateTo" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Semua</option>
                    <option value="ORDER">ORDER</option>
                    <option value="TERIMA">TERIMA</option>
                    <option value="BATAL">BATAL</option>
                </select>
            </div>
            <div class="col-span-6 md:col-span-2">
                <button wire:click="resetFilter" class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Reset Filter</button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Nomor Pembelian</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-left">Cabang</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center w-40">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembelianData as $index => $pembelian)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $pembelianData->firstItem() + $index }}</td>
                            <td class="px-4 py-3">{{ $pembelian->nomor_beli }}</td>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($pembelian->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $pembelian->supplier }}</td>
                            <td class="px-4 py-3">{{ $pembelian->cabang->nama_cabang ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($pembelian->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    if ($pembelian->status === 'ORDER') {
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($pembelian->status === 'TERIMA') {
                                        $statusClass = 'bg-green-100 text-green-800';
                                    } elseif ($pembelian->status === 'BATAL') {
                                        $statusClass = 'bg-red-100 text-red-800';
                                    } else {
                                        $statusClass = 'bg-gray-100 text-gray-800';
                                    }
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusClass }}">
                                    {{ $pembelian->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('transaksi.pembelian.edit', $pembelian->id) }}" wire:navigate class="px-3 py-1 bg-amber-500 text-white rounded">Edit</a>
                                    @if($pembelian->status === 'ORDER')
                                        <button wire:click="receivePembelian({{ $pembelian->id }})" class="px-3 py-1 bg-green-500 text-white rounded">Terima</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">Data pembelian belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $pembelianData->links() }}
    </div>
</div>
