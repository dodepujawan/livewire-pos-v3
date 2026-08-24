<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Detail Transaksi</h1>
            <p class="text-sm text-gray-500">Detail transaksi penjualan barang.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('transaksi.penjualan.edit', ['id' => $transaksiId]) }}" wire:navigate class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Edit
            </a>
            <a href="{{ route('transaksi.penjualan.list') }}" wire:navigate class="px-4 py-2 border rounded-lg hover:bg-gray-50">Kembali</a>
        </div>
    </div>

    {{-- Transaction Header --}}
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">No. Invoice</label>
                <p class="text-lg font-semibold">{{ $transaksi->nomor_transaksi }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal</label>
                <p class="text-lg font-semibold">{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Customer</label>
                <p class="text-lg font-semibold">{{ $transaksi->customer ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Grand Total</label>
                <p class="text-lg font-bold text-blue-600">{{ number_format($transaksi->grand_total, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Transaction Details --}}
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Item Transaksi</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left text-sm font-medium">No</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">Barang</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">Satuan</th>
                        <th class="px-4 py-3 text-right text-sm font-medium">Qty</th>
                        <th class="px-4 py-3 text-right text-sm font-medium">Harga</th>
                        <th class="px-4 py-3 text-right text-sm font-medium">Diskon</th>
                        <th class="px-4 py-3 text-right text-sm font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($transaksi->details as $index => $detail)
                        <tr>
                            <td class="px-4 py-3">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">{{ $detail->barang->nama_barang }}</td>
                            <td class="px-4 py-3">{{ $detail->satuan->nama_satuan }}</td>
                            <td class="px-4 py-3 text-right">{{ (int) $detail->qty }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($detail->diskon ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">Tidak ada item transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payment Summary --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Ringkasan Pembayaran</h2>
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Total Tagihan</label>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($transaksi->grand_total, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>
