<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Laporan Arus Kas</h1>
            <p class="text-sm text-gray-500">Mutasi uang masuk & keluar.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Total Masuk</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalMasuk, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Total Keluar</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($totalKeluar, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-sm text-gray-500">Net Cash Flow</p>
            <p class="text-2xl font-bold {{ $netFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($netFlow, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4 items-end">
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
                <label class="block text-sm font-medium mb-2">Sumber</label>
                <select wire:model.live="sumberFilter" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Semua Sumber</option>
                    <option value="PENJUALAN">Penjualan</option>
                    <option value="REFUND">Refund</option>
                    <option value="PELUNASAN_PIUTANG">Pelunasan Piutang</option>
                    <option value="PELUNASAN_HUTANG">Pelunasan Hutang</option>
                    <option value="SETOR">Setor</option>
                    <option value="TARIK">Tarik</option>
                    <option value="LAIN">Lainnya</option>
                </select>
            </div>
            <div class="col-span-6 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Tanggal Dari</label>
                <input type="date" wire:model.live="dateFrom" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-6 md:col-span-3">
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
                        <th class="px-4 py-3 text-left">Cabang</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Sumber</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasis as $mutasi)
                        <tr class="border-t {{ $mutasi->tipe === 'MASUK' ? 'bg-green-50' : 'bg-red-50' }}">
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($mutasi->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $mutasi->cabang->nama_cabang ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $mutasi->tipe === 'MASUK' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $mutasi->tipe }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $mutasi->sumber ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $mutasi->tipe === 'MASUK' ? 'text-green-700' : 'text-red-700' }}">
                                {{ $mutasi->tipe === 'MASUK' ? '+' : '-' }}{{ number_format($mutasi->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $mutasi->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Data arus kas belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $mutasis->links() }}</div>
</div>
