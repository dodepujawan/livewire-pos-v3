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
                <label class="block text-sm font-medium mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Semua Status</option>
                    <option value="DRAFT">Draft</option>
                    <option value="SELESAI">Selesai</option>
                    <option value="PIUTANG">Piutang</option>
                    <option value="BATAL">Batal</option>
                </select>
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

    @if (session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800">
            {{ session('error') }}
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
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Metode Bayar</th>
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
                            <td class="px-4 py-3 text-center">
                                @if($transaksi->status === 'SELESAI')
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Selesai</span>
                                @elseif($transaksi->status === 'PIUTANG')
                                    <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-700">Piutang</span>
                                @elseif($transaksi->status === 'DRAFT')
                                    <span class="inline-flex rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-semibold text-yellow-700">Draft</span>
                                @elseif($transaksi->status === 'BATAL')
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">Batal</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $transaksi->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $transaksi->metode_bayar ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($transaksi->grand_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('transaksi.penjualan.show', $transaksi->id) }}" wire:navigate class="px-3 py-1 bg-blue-500 text-white rounded">View</a>
                                    @if($transaksi->status !== 'BATAL')
                                        <a href="{{ route('transaksi.penjualan.edit', $transaksi->id) }}" wire:navigate class="px-3 py-1 bg-amber-500 text-white rounded">Edit</a>
                                        <button type="button" wire:click="openCancelModal({{ $transaksi->id }})" class="px-3 py-1 bg-red-500 text-white rounded">Batal</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">Data transaksi belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeCancelModal">
            <div class="w-full max-w-md mx-4 rounded-xl bg-white p-5 shadow-xl">
                <h2 class="text-lg font-bold text-gray-900">Konfirmasi Pembatalan</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Masukkan alasan pembatalan transaksi. Draft hanya akan di-soft-delete, sedangkan transaksi final akan mengembalikan stok.
                </p>

                <label class="block mt-4 text-sm font-medium text-gray-700">Alasan Pembatalan</label>
                <textarea wire:model="cancelReason" rows="3" maxlength="500"
                          class="mt-1 w-full border rounded-lg px-3 py-2 text-sm"
                          placeholder="Contoh: Pesanan dibatalkan oleh pelanggan"></textarea>
                @error('cancelReason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" wire:click="closeCancelModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Kembali
                    </button>
                    <button type="button" wire:click="confirmCancelTransaksi"
                            wire:loading.attr="disabled" wire:target="confirmCancelTransaksi"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <span wire:loading.remove wire:target="confirmCancelTransaksi">Konfirmasi Batal</span>
                        <span wire:loading wire:target="confirmCancelTransaksi">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $transaksiData->links() }}
    </div>
</div>
