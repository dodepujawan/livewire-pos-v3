<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Laporan Kas</h1>
            <p class="text-sm text-gray-500">Mutasi uang per cabang.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-12 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Cabang</label>
                <select wire:model.live="cabangId" class="w-full border rounded-lg px-3 py-2">
                    @foreach($listCabang as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Tanggal Dari</label>
                <input type="date" wire:model.live="tanggalDari" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Tanggal Sampai</label>
                <input type="date" wire:model.live="tanggalSampai" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-12 md:col-span-2">
                <button wire:click="resetFilter" class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Reset Filter</button>
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
                        <th class="px-4 py-3 text-left">Sumber</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-right">MASUK</th>
                        <th class="px-4 py-3 text-right">KELUAR</th>
                        <th class="px-4 py-3 text-right">Saldo Akhir</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kasData as $item)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $item->cabang->nama_cabang ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->sumber }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $item->tipe === 'MASUK' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $item->tipe }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right {{ $item->tipe === 'MASUK' ? 'text-green-600 font-semibold' : '' }}">
                                {{ $item->tipe === 'MASUK' ? number_format($item->jumlah, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right {{ $item->tipe === 'KELUAR' ? 'text-red-600 font-semibold' : '' }}">
                                {{ $item->tipe === 'KELUAR' ? number_format($item->jumlah, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">{{ number_format($item->saldo_akhir ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $item->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">Data kas belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $kasData->links() }}
    </div>
</div>
