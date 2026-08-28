<?php

use App\Models\Barang;
use App\Models\Cabang;
use App\Models\StokMutasi;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $searchKeyword = '';
    public string $cabangId = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    protected $rules = [
        'dateFrom' => 'nullable|date',
        'dateTo' => 'nullable|date|after_or_equal:dateFrom',
    ];

    public function updatedSearchKeyword() { $this->resetPage(); }
    public function updatedCabangId() { $this->resetPage(); }
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }

    public function resetFilter(): void
    {
        $this->searchKeyword = '';
        $this->cabangId = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        $baseQuery = StokMutasi::query()
            ->when($this->searchKeyword, function ($q) {
                $q->whereHas('barang', function ($bq) {
                    $bq->where('nama_barang', 'like', '%' . $this->searchKeyword . '%')
                       ->orWhere('kode_barang', 'like', '%' . strtoupper($this->searchKeyword) . '%');
                });
            })
            ->when($this->cabangId, fn($q) => $q->where('cabang_id', $this->cabangId))
            ->when($this->dateFrom, fn($q) => $q->whereDate('tanggal', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('tanggal', '<=', $this->dateTo));

        $totalMasuk = $baseQuery->where('tipe', 'MASUK')->sum('qty');
        $totalKeluar = $baseQuery->where('tipe', 'KELUAR')->sum('qty');

        $query = $baseQuery->with(['barang', 'cabang'])
            ->latest('tanggal')
            ->paginate(15);

        $listCabang = Cabang::where('is_aktif', true)->orderBy('nama_cabang')->get()->mapWithKeys(fn($c) => [$c->id => $c->nama_cabang])->toArray();
        $listBarang = Barang::select('id', 'nama_barang', 'kode_barang')->orderBy('nama_barang')->get()->mapWithKeys(fn($b) => [$b->id => $b->nama_barang])->toArray();

        return $this->view([
            'mutasis' => $query,
            'listCabang' => $listCabang,
            'listBarang' => $listBarang,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
        ])
        ->layout('layouts::app')
        ->title('Laporan Stok');
    }
};
