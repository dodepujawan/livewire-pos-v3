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
            <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-xl bg-white shadow-xl">
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
                                <p class="text-sm text-slate-600">Pilih salah satu dari tiga kartu, unduh file yang sesuai, isi file, lalu upload untuk divalidasi sebelum disimpan.</p>
                            </div>
                            <button type="button" wire:click="toggleBarangExcelGuide" class="shrink-0 text-sm font-medium text-blue-700 hover:underline">
                                {{ $showBarangExcelGuide ? 'Tutup Panduan' : 'Lihat Panduan' }}
                            </button>
                        </div>
                        @if($showBarangExcelGuide)
                            <div class="mt-3 space-y-2 border-t border-slate-200 pt-3 text-sm text-slate-700">
                                <p><strong>A. Tambah barang baru:</strong> unduh template kosong. Jika ingin memasukkan stok awal lebih dari nol, pilih cabang agar stok hanya masuk ke cabang tersebut. Jika baru menambah katalog, pilih “Tanpa stok awal”. Jangan ubah kolom `jenis_template`; salin nilainya saat menambah baris.</p>
                                <p><strong>B. Update data barang:</strong> unduh Export Data Barang, ubah nama/harga atau satuan, lalu upload lewat kartu Update Data Barang. Angka total stok pada export hanya informasi dan tidak diimpor kembali. Jangan ubah kolom `jenis_template`.</p>
                                <p><strong>Tambah satuan:</strong> pada file export, tambah baris dengan kode_barang yang sama dan nama satuan baru, lalu upload sebagai Update Data Barang.</p>
                                <p><strong>C. Penyesuaian stok cabang:</strong> cabang wajib dipilih; tidak ada pilihan untuk mengubah semua cabang sekaligus. Unduh template untuk cabang itu, isi stok_baru sebagai jumlah akhir hasil hitung fisik (bukan selisih), serta alasan. Baris tanpa stok_baru tidak diubah. Jangan ubah kolom `jenis_template` dan `cabang_template_id`; sistem memeriksa keduanya saat upload.</p>
                                <p>Jika aktivitas atau cabang penyesuaian stok diganti setelah file dipilih, file dikosongkan agar file lama tidak terproses dengan pilihan baru.</p>
                                <p class="text-amber-700"><strong>Catatan:</strong> setiap upload penyesuaian stok hanya memengaruhi cabang yang dipilih dan menulis riwayat mutasi stok.</p>
                            </div>
                        @endif
                    </div>

                    <div>
                        <p class="mb-3 text-sm font-semibold text-slate-700">Langkah 1: Pilih salah satu dari tiga aktivitas</p>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            <section class="rounded-xl border p-4 {{ $barangImportMode === 'new' ? 'border-blue-600 bg-blue-50' : 'border-slate-200' }}">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">A</span>
                                    <div>
                                        <h3 class="font-semibold text-slate-900">Tambah Barang Baru</h3>
                                        <p class="mt-1 text-xs text-slate-600">Untuk kode barang yang belum ada di katalog.</p>
                                    </div>
                                </div>
                                @if(auth()->user()->can('master.barang.import'))
                                    <button type="button" wire:click="selectBarangImportMode('new')" class="mt-3 w-full rounded border border-blue-600 px-3 py-2 text-sm text-blue-700 hover:bg-white">
                                        {{ $barangImportMode === 'new' ? 'Aktivitas dipilih' : 'Pilih aktivitas' }}
                                    </button>
                                @endif
                                @if(auth()->user()->can('master.barang.export'))
                                    <button type="button" wire:click="downloadBarangTemplate" class="mt-2 w-full rounded bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">
                                        Download Template Barang
                                    </button>
                                @endif
                                <div class="mt-3">
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Cabang stok awal (opsional)</label>
                                    <select wire:model="barangImportCabangId" class="w-full rounded border px-2 py-2 text-sm">
                                        <option value="0">Tanpa stok awal</option>
                                        @foreach($listCabang as $id => $nama)
                                            <option value="{{ $id }}">{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('barangImportCabangId')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </section>

                            <section class="rounded-xl border p-4 {{ $barangImportMode === 'update' ? 'border-indigo-600 bg-indigo-50' : 'border-slate-200' }}">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">B</span>
                                    <div>
                                        <h3 class="font-semibold text-slate-900">Update Data Barang</h3>
                                        <p class="mt-1 text-xs text-slate-600">Untuk mengubah nama/harga atau menambah satuan. Stok tidak berubah.</p>
                                    </div>
                                </div>
                                @if(auth()->user()->can('master.barang.import'))
                                    <button type="button" wire:click="selectBarangImportMode('update')" class="mt-3 w-full rounded border border-indigo-600 px-3 py-2 text-sm text-indigo-700 hover:bg-white">
                                        {{ $barangImportMode === 'update' ? 'Aktivitas dipilih' : 'Pilih aktivitas' }}
                                    </button>
                                @endif
                                @if(auth()->user()->can('master.barang.export'))
                                    <button type="button" wire:click="exportBarangData" class="mt-2 w-full rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700">
                                        Export Data untuk Diedit
                                    </button>
                                @endif
                            </section>

                            <section class="rounded-xl border p-4 {{ $barangImportMode === 'stock' ? 'border-amber-600 bg-amber-50' : 'border-slate-200' }}">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-600 text-sm font-semibold text-white">C</span>
                                    <div>
                                        <h3 class="font-semibold text-slate-900">Penyesuaian Stok Cabang</h3>
                                        <p class="mt-1 text-xs text-slate-600">Untuk memasukkan jumlah stok akhir hasil hitung fisik.</p>
                                    </div>
                                </div>
                                @if(auth()->user()->can('master.barang.import'))
                                    <button type="button" wire:click="selectBarangImportMode('stock')" class="mt-3 w-full rounded border border-amber-600 px-3 py-2 text-sm text-amber-700 hover:bg-white">
                                        {{ $barangImportMode === 'stock' ? 'Aktivitas dipilih' : 'Pilih aktivitas' }}
                                    </button>
                                @endif
                                <div class="mt-3">
                                    <label class="mb-1 block text-xs font-medium text-slate-700">Cabang yang dihitung</label>
                                    <select wire:model="barangStockCabangId" class="w-full rounded border px-2 py-2 text-sm">
                                        <option value="0">Pilih cabang</option>
                                        @foreach($listCabang as $id => $nama)
                                            <option value="{{ $id }}">{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('barangStockCabangId')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                @if(auth()->user()->can('master.barang.export'))
                                    <button type="button" wire:click="downloadStokCabangTemplate" class="mt-2 w-full rounded bg-amber-600 px-3 py-2 text-sm text-white hover:bg-amber-700">
                                        Download Template Stok Cabang
                                    </button>
                                @endif
                            </section>
                        </div>
                    </div>

                    <div class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <strong>Langkah 2:</strong> Download file dari kartu yang dipilih. Isi atau edit file tersebut, lalu lanjut ke upload di bawah.
                    </div>

                    @if(auth()->user()->can('master.barang.import'))
                        <form wire:submit="importBarang" class="space-y-4 border-t pt-5">
                            <div>
                                <p class="mb-2 text-sm font-semibold text-slate-700">Langkah 3: Upload file untuk aktivitas yang dipilih</p>
                                <p class="mb-2 text-xs text-slate-500">
                                    @if($barangImportMode === 'new')
                                        File harus memakai template barang baru. Jika mengisi stok, stok masuk ke cabang stok awal di atas.
                                    @elseif($barangImportMode === 'update')
                                        Gunakan file hasil Export Data untuk mengubah katalog; stok tidak disentuh.
                                    @else
                                        Gunakan template stok dari cabang yang dipilih. Isi stok_baru sebagai jumlah akhir dan alasan perubahan.
                                    @endif
                                </p>
                                <label class="mb-2 block text-sm font-medium">File Excel</label>
                                <input type="file" wire:model="barangImportFile" accept=".xlsx,.xls" class="w-full rounded-lg border px-3 py-2">
                                @if($barangImportMode !== 'stock')
                                    <p class="mt-1 text-xs text-gray-500">Satu baris untuk satu satuan. Barang multi-satuan memakai kode barang yang sama.</p>
                                @endif
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
