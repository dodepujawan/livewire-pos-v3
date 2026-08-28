<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Daftar Hutang</h1>
            <p class="text-sm text-gray-500">Hutang ke supplier yang belum lunas.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-12 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Cari Supplier/Nomor</label>
                <input type="text" wire:model.live.debounce.300ms="searchKeyword" placeholder="Nama atau nomor hutang..." class="w-full border rounded-lg px-3 py-2">
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
                    <option value="BELUM_LUNAS">Belum Lunas</option>
                    <option value="LUNAS">Lunas</option>
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
                        <th class="px-4 py-3 text-left">No. Hutang</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-left">Cabang</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-right">Sisa</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hutangs as $hutang)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-mono text-xs">{{ $hutang->nomor_hutang }}</td>
                            <td class="px-4 py-3">{{ $hutang->supplier }}</td>
                            <td class="px-4 py-3">{{ $hutang->cabang->nama_cabang ?? '-' }}</td>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($hutang->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($hutang->jumlah, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ (float) $hutang->sisa > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($hutang->sisa, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $hutang->status === 'LUNAS' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $hutang->status === 'LUNAS' ? 'LUNAS' : 'BELUM LUNAS' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($hutang->status === 'BELUM_LUNAS')
                                    <button wire:click="openPelunasanModal({{ $hutang->id }})" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                        Bayar
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">Data hutang belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $hutangs->links() }}
    </div>

    {{-- Pelunasan Modal --}}
    @if($showPelunasanModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closePelunasanModal()">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold mb-4">Pelunasan Hutang</h2>

                @if(session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <form wire:submit.prevent="processPelunasan">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Jumlah Bayar</label>
                            <input type="number" wire:model.live="pelunasanJumlah" min="0.01" step="0.01" class="w-full border rounded-lg px-3 py-2" autofocus>
                            @error('pelunasanJumlah')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Metode Bayar</label>
                            <select wire:model.live="pelunasanMetodeBayar" class="w-full border rounded-lg px-3 py-2">
                                <option value="TUNAI">Tunai</option>
                                <option value="TRANSFER">Transfer</option>
                                <option value="QRIS">QRIS</option>
                            </select>
                            @error('pelunasanMetodeBayar')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Catatan</label>
                            <textarea wire:model.live="pelunasanCatatan" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2 justify-end">
                        <button type="button" wire:click="closePelunasanModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Konfirmasi Pelunasan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
