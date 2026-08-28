<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Laporan Penjualan</h1>
            <p class="text-sm text-gray-500">Ringkasan penjualan per invoice.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Total Penjualan</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($totalPenjualan, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Total Pajak</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($totalPajak, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Total Diskon</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($totalDiskon, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-12 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Cari Invoice/Customer</label>
                <input type="text" wire:model.live.debounce.300ms="searchKeyword" placeholder="Nomor atau customer..." class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Cabang</label>
                <select wire:model.live="cabangId" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Semua Cabang</option>
                    @foreach($listCabang as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Semua Status</option>
                    <option value="SELESAI">Selesai</option>
                    <option value="PIUTANG">Piutang</option>
                    <option value="BATAL">Batal</option>
                </select>
            </div>
            <div class="col-span-6 md:col-span-2">
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
                        <th class="px-4 py-3 text-left">No. Invoice</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Cabang</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                        <th class="px-4 py-3 text-right">Diskon</th>
                        <th class="px-4 py-3 text-right">Pajak</th>
                        <th class="px-4 py-3 text-right">Grand Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $transaksi)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $transaksi->nomor_transaksi }}</td>
                            <td class="px-4 py-3">{{ $transaksi->customer ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $transaksi->cabang->nama_cabang ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($transaksi->grand_total - (float)$transaksi->diskon_total - (float)$transaksi->pajak, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-red-600">{{ number_format($transaksi->diskon_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-orange-600">{{ number_format($transaksi->pajak, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ number_format($transaksi->grand_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $transaksi->status === 'SELESAI' ? 'bg-green-100 text-green-800' : ($transaksi->status === 'PIUTANG' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $transaksi->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format($transaksi->bayar, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-8 text-gray-500">Data penjualan belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $transaksis->links() }}</div>
</div>
