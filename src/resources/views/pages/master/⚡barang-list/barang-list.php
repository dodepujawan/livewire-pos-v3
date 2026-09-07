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

        return Excel::download(new BarangTemplateExport(), 'template-import-barang.xlsx');
    }

    public function exportBarangData()
    {
        abort_unless(auth()->user()->can('master.barang.export'), 403);

        return Excel::download(new BarangExport(), 'data-barang.xlsx');
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
                if ($code === '' || $name === '' || $unitName === '') {
                    $errors[] = "Baris {$line}: kode, nama barang, dan nama satuan wajib diisi.";
                    continue;
                }
                if (! is_numeric($row['stok'] ?? null) || (int) $row['stok'] < 0 || (float) $row['stok'] != (int) $row['stok']) {
                    $errors[] = "Baris {$line}: stok harus berupa bilangan bulat minimal 0.";
                    continue;
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

                $defaultValue = strtolower(trim((string) ($row['is_default'] ?? '')));
                if (! in_array($defaultValue, ['0', '1', 'ya', 'tidak', 'yes', 'no', 'true', 'false'], true)) {
                    $errors[] = "Baris {$line}: is_default harus 1/0 atau ya/tidak.";
                }

                $groups[$code][] = [
                    'line' => $line,
                    'kode_barang' => $code,
                    'nama_barang' => $name,
                    'stok' => (int) $row['stok'],
                    'nama_satuan' => $unitName,
                    'konversi' => (int) $row['konversi'],
                    'harga_jual' => (float) ($row['harga_jual'] ?? 0),
                    'harga_beli' => (float) ($row['harga_beli'] ?? 0),
                    'is_default' => in_array($defaultValue, ['1', 'ya', 'yes', 'true'], true),
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
                $defaultCount = 0;
                $unitNames = [];
                $hasBaseUnit = false;
                foreach ($items as $item) {
                    if ($item['nama_barang'] !== $first['nama_barang'] || $item['stok'] !== $first['stok']) {
                        $errors[] = "Kode {$code}: nama barang dan stok harus sama pada semua barisnya.";
                    }
                    if (in_array($item['nama_satuan'], $unitNames, true)) {
                        $errors[] = "Baris {$item['line']}: satuan {$item['nama_satuan']} duplikat untuk {$code}.";
                    }
                    $unitNames[] = $item['nama_satuan'];
                    $defaultCount += $item['is_default'] ? 1 : 0;
                    $hasBaseUnit = $hasBaseUnit || $item['konversi'] === 1;
                }
                if ($defaultCount !== 1) {
                    $errors[] = "Kode {$code}: harus memiliki tepat satu satuan default.";
                }
                if (! $hasBaseUnit) {
                    $errors[] = "Kode {$code}: harus memiliki satuan dengan konversi 1.";
                }
            }

            if ($errors !== []) {
                $this->addError('barangImportFile', implode(' ', $errors));
                return;
            }

            DB::transaction(function () use ($groups): void {
                foreach ($groups as $items) {
                    $master = $items[0];
                    $defaultUnit = collect($items)->firstWhere('is_default', true);
                    $barang = $this->barangImportMode === 'update'
                        ? Barang::where('kode_barang', $master['kode_barang'])->firstOrFail()
                        : Barang::create([
                            'kode_barang' => $master['kode_barang'],
                            'nama_barang' => $master['nama_barang'],
                            'stok' => $master['stok'],
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
                            'is_default' => $item['is_default'],
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
