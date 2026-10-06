<div class="p-1">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Master Barang</h1>
            <p class="text-sm text-gray-500">Daftar barang dan stok.</p>
        </div>
        <div class="flex flex-wrap gap-2 justify-end">
            @if(auth()->user()->can('master.barang.import') || auth()->user()->can('master.barang.export'))
                <button type="button" wire:click="openBarangExcelModal" class="px-4 py-2 border border-emerald-600 text-emerald-700 rounded-lg hover:bg-emerald-50">
                    Excel Barang
                </button>
            @endif
            <a href="{{ route('master.barang.create') }}" wire:navigate class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Tambah Barang</a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow p-4 mb-5">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-4">
                <label class="block text-sm font-medium mb-2">Cari Barang</label>
                <input type="text" wire:model.live.debounce.300ms="searchBarangKeyword" class="w-full border rounded-lg px-3 py-2" placeholder="Kode / Nama Barang...">
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
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Kode Barang</th>
                        <th class="px-4 py-3 text-left">Nama Barang</th>
                        <th class="px-4 py-3 text-left">Satuan</th>
                        <th class="px-4 py-3 text-right">Total Stok Semua Cabang</th>
                        <th class="px-4 py-3 text-center w-40">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangData as $index => $barang)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $barangData->firstItem() + $index }}</td>
                            <td class="px-4 py-3">{{ $barang->kode_barang }}</td>
                            <td class="px-4 py-3">{{ $barang->nama_barang }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($barang->satuan as $satuan)
                                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                            {{ $satuan->nama_satuan }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format((int) $barang->stok_total) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('master.barang.edit', $barang->id) }}" wire:navigate class="px-3 py-1 bg-amber-500 text-white rounded">Edit</a>
                                    <button type="button" onclick="confirm('Yakin hapus barang ini?') || event.stopImmediatePropagation()" wire:click="deleteBarang({{ $barang->id }})"class="px-3 py-1 bg-red-600 text-white rounded">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Data barang belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $barangData->links() }}
    </div>

    @if($showBarangExcelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold">Excel Barang</h2>
                        <p class="text-sm text-gray-500">Pilih kebutuhan Anda. Sistem akan membantu menentukan alurnya.</p>
                    </div>
                    <button type="button" wire:click="closeBarangExcelModal" class="text-2xl text-gray-400 hover:text-gray-700">&times;</button>
                </div>

                <div class="space-y-5 p-6">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-800">Cara kerja Excel Barang</p>
                                <p class="text-sm text-slate-600">Pilih aktivitas, isi atau edit file, upload, lalu sistem memvalidasi sebelum menyimpan.</p>
                            </div>
                            <button type="button" wire:click="toggleBarangExcelGuide" class="shrink-0 text-sm font-medium text-blue-700 hover:underline">
                                {{ $showBarangExcelGuide ? 'Tutup Panduan' : 'Lihat Panduan' }}
                            </button>
                        </div>
                        @if($showBarangExcelGuide)
                            <div class="mt-3 space-y-2 border-t border-slate-200 pt-3 text-sm text-slate-700">
                                <p><strong>Tambah barang baru:</strong> download template kosong, pilih cabang stok awal, lalu isi stok opsional pada file. Stok hanya ditempatkan pada cabang terpilih.</p>
                                <p><strong>Ubah data lama:</strong> export data barang, edit file hasil export, lalu pilih mode Update Barang Existing. Angka total stok di export hanya informasi.</p>
                                <p><strong>Tambah satuan:</strong> pada file export, tambahkan baris di bawah barang terkait dengan kode_barang yang sama, lalu gunakan mode Update Barang Existing.</p>
                                <p><strong>Penyesuaian stok:</strong> pilih cabang, download template cabang tersebut, isi stok_baru sebagai jumlah akhir dan alasan. Baris tanpa stok_baru tidak diubah.</p>
                                <p class="text-amber-700"><strong>Catatan:</strong> mode Update Barang Existing tidak mengubah stok. Penyesuaian Excel dicatat ke riwayat stok cabang.</p>
                            </div>
                        @endif
                    </div>

                    <p class="text-sm font-semibold text-slate-700">Saya ingin...</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @if(auth()->user()->can('master.barang.export'))
                            <button type="button" wire:click="downloadBarangTemplate" class="rounded-lg border border-blue-600 px-4 py-3 text-left text-blue-700 hover:bg-blue-50">
                                <span class="block font-semibold">Template Barang Baru</span>
                                <span class="text-xs">File kosong untuk menambah kode barang baru.</span>
                            </button>
                            <button type="button" wire:click="exportBarangData" class="rounded-lg border border-indigo-600 px-4 py-3 text-left text-indigo-700 hover:bg-indigo-50">
                                <span class="block font-semibold">Export Data Barang</span>
                                <span class="text-xs">Download data barang yang sudah ada.</span>
                            </button>
                        @endif
                    </div>

                    @if(auth()->user()->can('master.barang.import'))
                        <form wire:submit="importBarang" class="space-y-4 border-t pt-5">
                            <div>
                                <label class="mb-2 block text-sm font-medium">Saya ingin</label>
                                <select wire:model.live="barangImportMode" class="w-full rounded-lg border px-3 py-2">
                                    <option value="new">Import Barang Baru</option>
                                    <option value="update">Update atau Tambah Satuan Barang Existing</option>
                                    <option value="stock">Penyesuaian Stok per Cabang</option>
                                </select>
                            </div>
                            @if(in_array($barangImportMode, ['new', 'stock'], true))
                                <div>
                                    <label class="mb-2 block text-sm font-medium">{{ $barangImportMode === 'stock' ? 'Cabang yang Disesuaikan' : 'Cabang untuk Stok Awal' }}</label>
                                    <select wire:model="barangImportCabangId" class="w-full rounded-lg border px-3 py-2">
                                        <option value="0">{{ $barangImportMode === 'stock' ? 'Pilih cabang' : 'Tanpa stok awal (katalog saja)' }}</option>
                                        @foreach($listCabang as $id => $nama)
                                            <option value="{{ $id }}">{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('barangImportCabangId')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                            @if($barangImportMode === 'stock' && auth()->user()->can('master.barang.export'))
                                <button type="button"
                                        wire:click="downloadStokCabangTemplate"
                                        class="rounded-lg border border-indigo-600 px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50">
                                    Download Template Stok Cabang
                                </button>
                            @endif
                            <div>
                                <label class="mb-2 block text-sm font-medium">File Excel</label>
                                <input type="file" wire:model="barangImportFile" accept=".xlsx,.xls" class="w-full rounded-lg border px-3 py-2">
                                <p class="mt-1 text-xs text-gray-500">Satu baris untuk satu satuan. Barang multi-satuan memakai kode barang yang sama.</p>
                                @if($barangImportMode === 'update')
                                    <p class="mt-1 text-xs text-amber-600">Gunakan file hasil Export Data Barang. Untuk menambah satuan, tambahkan baris dengan kode barang yang sama. Stok tidak diubah.</p>
                                @elseif($barangImportMode === 'stock')
                                    <p class="mt-1 text-xs text-amber-600">Isi stok_baru sebagai jumlah akhir di cabang terpilih. Setiap perubahan stok wajib diberi alasan.</p>
                                @endif
                                @error('barangImportFile')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="closeBarangExcelModal" class="rounded-lg border px-4 py-2">Batal</button>
                                <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-emerald-600 px-4 py-2 text-white disabled:opacity-50">
                                    <span wire:loading.remove wire:target="importBarang">Validasi dan Proses</span>
                                    <span wire:loading wire:target="importBarang">Memproses...</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
