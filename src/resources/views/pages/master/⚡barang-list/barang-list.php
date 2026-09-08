<?php

use App\Exports\BarangTemplateExport;
use App\Exports\BarangExport;
use App\Imports\BarangImport;
use App\Models\Barang;
use App\Models\BarangSatuan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected array $additionalPermissions = [
        'master.barang.delete',
        'master.barang.import',
        'master.barang.export',
    ];

    public string $searchBarangKeyword = '';
    public $barangImportFile;
    public bool $showBarangExcelModal = false;
    public bool $showBarangExcelGuide = false;
    public string $barangImportMode = 'new';

    public function updatingSearchBarangKeyword()
    {
        $this->resetPage();
    }

    public function deleteBarang(int $barangId)
    {
        abort_unless(auth()->user()->can('master.barang.delete'), 403);

        Barang::findOrFail($barangId)->delete();

        session()->flash(
            'success',
            'Barang berhasil dihapus'
        );
    }

    public function downloadBarangTemplate()
    {
        abort_unless(auth()->user()->can('master.barang.export'), 403);

        return Excel::download(new BarangTemplateExport(), '1-tambah-barang-baru.xlsx');
    }

    public function exportBarangData()
    {
        abort_unless(auth()->user()->can('master.barang.export'), 403);

        return Excel::download(new BarangExport(), '2-edit-atau-tambah-satuan-barang.xlsx');
    }

    public function openBarangExcelModal(): void
    {
        $this->resetValidation();
        $this->barangImportMode = 'new';
        $this->showBarangExcelModal = true;
    }

    public function closeBarangExcelModal(): void
    {
        $this->resetValidation();
        $this->reset('barangImportFile');
        $this->showBarangExcelGuide = false;
        $this->showBarangExcelModal = false;
    }

    public function selectBarangImportMode(string $mode): void
    {
        if (! in_array($mode, ['new', 'update'], true)) {
            return;
        }

        $this->barangImportMode = $mode;
        $this->resetValidation();
    }

    public function toggleBarangExcelGuide(): void
    {
        $this->showBarangExcelGuide = ! $this->showBarangExcelGuide;
    }

    public function importBarang()
    {
        abort_unless(auth()->user()->can('master.barang.import'), 403);

        $this->validate([
            'barangImportFile' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'barangImportFile.required' => 'Pilih file Excel terlebih dahulu.',
            'barangImportFile.mimes' => 'File harus berformat XLS atau XLSX.',
        ]);

        try {
            $rows = Excel::toCollection(new BarangImport(), $this->barangImportFile)->first() ?? collect();
            $groups = [];
            $errors = [];

            foreach ($rows as $rowIndex => $row) {
                $line = $rowIndex + 2;
                $code = strtoupper(trim((string) ($row['kode_barang'] ?? '')));
                $name = trim((string) ($row['nama_barang'] ?? ''));
                $unitName = strtoupper(trim((string) ($row['nama_satuan'] ?? '')));

                if ($code === '' && $name === '' && $unitName === '') {
                    continue;
                }
                if (str_starts_with($code, '=')) {
                    $errors[] = "Baris {$line}: kode_barang masih berupa formula ({$code}). Copy kolom kode, lalu gunakan Paste Special > Values Only sebelum upload.";
                    continue;
                }
                if ($code === '' || $name === '' || $unitName === '') {
                    $errors[] = "Baris {$line}: kode, nama barang, dan nama satuan wajib diisi.";
                    continue;
                }
                $stockValue = $row['stok'] ?? null;
                if ($stockValue !== null && trim((string) $stockValue) !== '') {
                    if (! is_numeric($stockValue) || (int) $stockValue < 0 || (float) $stockValue != (int) $stockValue) {
                        $errors[] = "Baris {$line}: stok harus berupa bilangan bulat minimal 0.";
                        continue;
                    }
                    $stockValue = (int) $stockValue;
                } else {
                    $stockValue = null;
                }
                if (! is_numeric($row['konversi'] ?? null) || (int) $row['konversi'] < 1 || (float) $row['konversi'] != (int) $row['konversi']) {
                    $errors[] = "Baris {$line}: konversi harus berupa bilangan bulat minimal 1.";
                    continue;
                }

                foreach (['harga_jual', 'harga_beli'] as $priceColumn) {
                    if (! is_numeric($row[$priceColumn] ?? null) || (float) $row[$priceColumn] < 0) {
                        $errors[] = "Baris {$line}: {$priceColumn} harus berupa angka minimal 0.";
                    }
                }

                $groups[$code][] = [
                    'line' => $line,
                    'kode_barang' => $code,
                    'nama_barang' => $name,
                    'stok' => $stockValue,
                    'nama_satuan' => $unitName,
                    'konversi' => (int) $row['konversi'],
                    'harga_jual' => (float) ($row['harga_jual'] ?? 0),
                    'harga_beli' => (float) ($row['harga_beli'] ?? 0),
                ];
            }

            if ($groups === []) {
                $errors[] = 'File Excel tidak memiliki data barang.';
            }

            $existingCodes = Barang::whereIn('kode_barang', array_keys($groups))
                ->pluck('kode_barang')->map(fn ($code) => strtoupper($code))->all();

            foreach ($groups as $code => $items) {
                if ($this->barangImportMode === 'new' && in_array($code, $existingCodes, true)) {
                    $errors[] = "Kode barang {$code} sudah terdaftar untuk mode barang baru.";
                }
                if ($this->barangImportMode === 'update' && ! in_array($code, $existingCodes, true)) {
                    $errors[] = "Kode barang {$code} belum terdaftar untuk mode update.";
                }

                $first = $items[0];
                $unitNames = [];
                $minimumConversion = min(array_column($items, 'konversi'));
                $minimumUnits = array_filter($items, fn ($item) => $item['konversi'] === $minimumConversion);
                $stockValues = array_values(array_unique(array_filter(array_column($items, 'stok'), fn ($stock) => $stock !== null)));
                foreach ($items as $item) {
                    if ($item['nama_barang'] !== $first['nama_barang']) {
                        $errors[] = "Kode {$code}: nama barang harus sama pada semua barisnya.";
                    }
                    if (in_array($item['nama_satuan'], $unitNames, true)) {
                        $errors[] = "Baris {$item['line']}: satuan {$item['nama_satuan']} duplikat untuk {$code}.";
                    }
                    $unitNames[] = $item['nama_satuan'];
                }
                if (count($minimumUnits) !== 1) {
                    $errors[] = "Kode {$code}: harus memiliki tepat satu satuan dengan konversi terkecil ({$minimumConversion}).";
                }
                if ($minimumConversion !== 1) {
                    $errors[] = "Kode {$code}: satuan dengan konversi terkecil harus memiliki nilai konversi 1.";
                }
                if (count($stockValues) > 1) {
                    $errors[] = "Kode {$code}: stok tidak boleh berbeda pada beberapa baris. Isi stok hanya pada satuan dengan konversi terkecil.";
                }
            }

            if ($errors !== []) {
                $this->addError('barangImportFile', implode(' ', $errors));
                return;
            }

            DB::transaction(function () use ($groups): void {
                foreach ($groups as $items) {
                    $master = $items[0];
                    $minimumConversion = min(array_column($items, 'konversi'));
                    $defaultUnit = collect($items)->firstWhere('konversi', $minimumConversion);
                    $minimumStockRow = collect($items)->first(fn ($item) => $item['konversi'] === $minimumConversion && $item['stok'] !== null);
                    $anyStockRow = collect($items)->first(fn ($item) => $item['stok'] !== null);
                    $stockValue = $minimumStockRow['stok'] ?? $anyStockRow['stok'] ?? 0;
                    $barang = $this->barangImportMode === 'update'
                        ? Barang::where('kode_barang', $master['kode_barang'])->firstOrFail()
                        : Barang::create([
                            'kode_barang' => $master['kode_barang'],
                            'nama_barang' => $master['nama_barang'],
                            'stok' => $stockValue,
                            'harga_beli' => $defaultUnit['harga_beli'],
                        ]);

                    if ($this->barangImportMode === 'update') {
                        $barang->update([
                            'nama_barang' => $master['nama_barang'],
                            'harga_beli' => $defaultUnit['harga_beli'],
                        ]);
                    }

                    foreach ($items as $item) {
                        $satuan = $this->barangImportMode === 'update'
                            ? $barang->satuan()->where('nama_satuan', $item['nama_satuan'])->first()
                            : null;
                        $satuanData = [
                            'nama_satuan' => $item['nama_satuan'],
                            'konversi' => $item['konversi'],
                            'harga_jual' => $item['harga_jual'],
                            'harga_beli' => $item['harga_beli'],
                            'is_default' => $item['konversi'] === $minimumConversion,
                        ];

                        if ($satuan) {
                            $satuan->update($satuanData);
                        } else {
                            $barang->satuan()->create($satuanData);
                        }
                    }
                }
            });

            $modeLabel = $this->barangImportMode === 'update' ? 'diupdate' : 'diimport';
            $this->closeBarangExcelModal();
            session()->flash('success', count($groups) . " barang berhasil {$modeLabel}.");
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('barangImportFile', 'File tidak dapat diproses. Periksa template dan isinya.');
        }
    }

    public function render()
    {
        $barangData = Barang::with('satuan')
        ->when(
            $this->searchBarangKeyword,
            function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where(
                        'nama_barang',
                        'like',
                        '%' . $this->searchBarangKeyword . '%'
                    )
                    ->orWhere(
                        'kode_barang',
                        'like',
                        '%' . $this->searchBarangKeyword . '%'
                    );
                });
            }
        )
        ->latest()
        ->paginate(15);

        return $this->view([
            'barangData' => $barangData
        ])
        ->layout('layouts::app')
        ->title('Master Barang');
    }
};
