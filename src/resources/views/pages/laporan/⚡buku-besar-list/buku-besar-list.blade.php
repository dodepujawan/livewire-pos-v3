<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Buku Besar</h1>
            <p class="text-sm text-gray-500">Semua jurnal per akun + saldo.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-12 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Cari Nomor/Keterangan</label>
                <input type="text" wire:model.live.debounce.300ms="searchKeyword" placeholder="Nomor atau keterangan..." class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="block text-sm font-medium mb-2">Tipe Akun</label>
                <select wire:model.live="akunTipe" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Semua Tipe</option>
                    @foreach($akunTypes as $tipe)
                        <option value="{{ $tipe }}">{{ $tipe }}</option>
                    @endforeach
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
                        <th class="px-4 py-3 text-left">No. Jurnal</th>
                        <th class="px-4 py-3 text-left">Akun</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Kredit</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @if($jurnals->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Data jurnal belum tersedia.</td>
                        </tr>
                    @else
                        @foreach($jurnals as $jurnal)
                            @foreach($jurnal->details as $detail)
                                <tr class="border-t">
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $jurnal->nomor_jurnal }}</td>
                                    <td class="px-4 py-3">{{ $detail->akun->nama_akun ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right {{ $detail->debit > 0 ? 'text-green-600' : '' }}">
                                        {{ $detail->debit > 0 ? number_format($detail->debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right {{ $detail->kredit > 0 ? 'text-red-600' : '' }}">
                                        {{ $detail->kredit > 0 ? number_format($detail->kredit, 0, ',', '.') : '-' }}
                                    </td>
                                    @if($loop->first)
                                        <td class="px-4 py-3">{{ $jurnal->keterangan ?? '-' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $jurnals->links() }}
    </div>
</div>
