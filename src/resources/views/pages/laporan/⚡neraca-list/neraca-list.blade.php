<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Laporan Neraca</h1>
            <p class="text-sm text-gray-500">Posisi keuangan per {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-6 md:col-span-4">
                <label class="block text-sm font-medium mb-2">Tanggal Posisi</label>
                <input type="date" wire:model.live="dateTo" class="w-full border rounded-lg px-3 py-2">
            </div>
        </div>
    </div>

    {{-- ASET --}}
    <div class="bg-white rounded-xl shadow overflow-hidden mb-6">
        <div class="bg-blue-600 text-white px-4 py-3 font-bold">ASET</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Akun</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Kredit</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($akunList as $akun)
                        @if($akun['tipe'] === 'ASET')
                            <tr class="border-t">
                                <td class="px-4 py-2 font-mono text-xs">{{ $akun['kode_akun'] }}</td>
                                <td class="px-4 py-2">{{ $akun['nama_akun'] }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($akun['debit'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($akun['kredit'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right font-semibold {{ $akun['saldo'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ number_format(abs($akun['saldo']), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="border-t-2 bg-blue-50 font-bold">
                        <td colspan="3" class="px-4 py-3 text-right">Total ASET</td>
                        <td class="px-4 py-3 text-right text-blue-700">{{ number_format($aset, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- UTANG + MODAL + LABA --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="bg-purple-600 text-white px-4 py-3 font-bold">UTANG + MODAL + LABA</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Akun</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Kredit</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($akunList as $akun)
                        @if(in_array($akun['tipe'], ['UTANG', 'MODAL']))
                            <tr class="border-t">
                                <td class="px-4 py-2 font-mono text-xs">{{ $akun['kode_akun'] }}</td>
                                <td class="px-4 py-2">{{ $akun['nama_akun'] }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($akun['debit'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($akun['kredit'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-purple-700">
                                    {{ number_format(abs($akun['saldo']), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="border-t-2 bg-purple-50 font-bold">
                        <td colspan="3" class="px-4 py-3 text-right">Total UTANG + MODAL</td>
                        <td class="px-4 py-3 text-right text-purple-700">{{ number_format($utang + $modal, 0, ',', '.') }}</td>
                    </tr>

                    <tr class="border-t bg-orange-50 font-bold">
                        <td colspan="3" class="px-4 py-3 text-right">Pendapatan</td>
                        <td class="px-4 py-3 text-right text-green-700">{{ number_format($pendapatan, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-t bg-orange-50 font-bold">
                        <td colspan="3" class="px-4 py-3 text-right">Beban</td>
                        <td class="px-4 py-3 text-right text-red-700">{{ number_format($beban, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-t-2 bg-orange-100 font-bold">
                        <td colspan="3" class="px-4 py-3 text-right">Laba Bersih</td>
                        <td class="px-4 py-3 text-right {{ $laba >= 0 ? 'text-green-800' : 'text-red-800' }}">{{ number_format($laba, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-t-2 bg-purple-100 font-bold">
                        <td colspan="3" class="px-4 py-3 text-right">Total UTANG + MODAL + LABA</td>
                        <td class="px-4 py-3 text-right text-purple-800">{{ number_format($totalKanan, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Balance Check --}}
    <div class="mt-4 bg-white rounded-xl shadow p-4">
        <div class="flex justify-between items-center">
            <span class="text-sm font-semibold">Balance Check:</span>
            <span class="text-lg font-bold {{ abs($aset - $totalKanan) < 0.01 ? 'text-green-700' : 'text-red-700' }}">
                ASET {{ number_format($aset, 0, ',', '.') }} = UTANG+MODAL+LABA {{ number_format($totalKanan, 0, ',', '.') }}
                @if(abs($aset - $totalKanan) < 0.01)
                    ✅ SEIMBANG
                @else
                    ❌ TIDAK SEIMBANG (selisih: {{ number_format(abs($aset - $totalKanan), 0, ',', '.') }})
                @endif
            </span>
        </div>
    </div>
</div>
