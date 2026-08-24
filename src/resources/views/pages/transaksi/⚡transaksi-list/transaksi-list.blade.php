<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Transaksi Penjualan</h1>
            <p class="text-sm text-gray-500">Daftar transaksi penjualan barang.</p>
        </div>
        <a href="{{ route('transaksi.penjualan.create') }}" wire:navigate class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Buat Transaksi</a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-12 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Cari Invoice</label>
                <input type="text" wire:model.live.debounce.300ms="searchKeyword" class="w-full border rounded-lg px-3 py-2" placeholder="Nomor Invoice...">
            </div>
            <div class="col-span-12 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Tanggal Dari</label>
                <input type="date" wire:model.live="dateFrom" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-12 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Tanggal Sampai</label>
                <input type="date" wire:model.live="dateTo" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-12 md:col-span-2">
                <button wire:click="resetFilter" class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Reset Filter</button>
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
        {{-- Loading State --}}
        <div wire:loading.delay class="flex items-center justify-center py-8">
            <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-gray-600">Memuat data...</span>
        </div>

        {{-- Table Content --}}
        <div wire:loading.remove.delay class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Nomor Transaksi</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-right">Grand Total</th>
                        <th class="px-4 py-3 text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiData as $index => $transaksi)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $transaksiData->firstItem() + $index }}</td>
                            <td class="px-4 py-3">{{ $transaksi->nomor_transaksi }}</td>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $transaksi->customer ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($transaksi->grand_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('transaksi-show', $transaksi->id) }}" wire:navigate class="px-3 py-1 bg-blue-500 text-white rounded">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Data transaksi belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $transaksiData->links() }}
    </div>
</div>
