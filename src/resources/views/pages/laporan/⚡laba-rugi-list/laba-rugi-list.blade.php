<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Laporan Laba Rugi</h1>
            <p class="text-sm text-gray-500">Pendapatan - Beban - HPP per periode.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-6 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Tanggal Dari</label>
                <input type="date" wire:model.live="dateFrom" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-6 md:col-span-3">
                <label class="block text-sm font-medium mb-2">Tanggal Sampai</label>
                <input type="date" wire:model.live="dateTo" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="col-span-12 md:col-span-3">
                <button wire:click="resetFilter" class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Reset Filter</button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Akun</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labaRugi as $item)
                        <tr class="border-t {{ $item['tipe'] === 'PENDAPATAN' ? 'bg-blue-50' : 'bg-orange-50' }}">
                            <td class="px-4 py-3 font-mono">{{ $item['kode_akun'] }}</td>
                            <td class="px-4 py-3">{{ $item['nama_akun'] }}</td>
                            <td class="px-4 py-3 text-right {{ $item['debit'] > 0 ? 'text-green-600' : '' }}">
                                {{ $item['debit'] > 0 ? number_format($item['debit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right {{ $item['kredit'] > 0 ? 'text-red-600' : '' }}">
                                {{ $item['kredit'] > 0 ? number_format($item['kredit'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                    <tr class="border-t font-bold bg-gray-50">
                        <td colspan="2" class="px-4 py-3 text-right">Total Pendapatan</td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3 text-right text-green-700">{{ number_format($pendapatanTotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-t font-bold bg-gray-50">
                        <td colspan="2" class="px-4 py-3 text-right">Total Beban</td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3 text-right text-red-700">{{ number_format($bebanTotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-t-2 font-bold {{ $labaRugiNeto >= 0 ? 'bg-green-100' : 'bg-red-100' }}">
                        <td colspan="3" class="px-4 py-3 text-right text-lg">Laba / (Rugi) Bersih</td>
                        <td class="px-4 py-3 text-right text-lg {{ $labaRugiNeto >= 0 ? 'text-green-800' : 'text-red-800' }}">{{ number_format($labaRugiNeto, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
