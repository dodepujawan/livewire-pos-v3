<?php

use App\Models\Cabang;
use App\Models\KasMutasi;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $cabangId = '';
    public string $sumberFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    protected $rules = [
        'dateFrom' => 'nullable|date',
        'dateTo' => 'nullable|date|after_or_equal:dateFrom',
    ];

    public function updatedCabangId() { $this->resetPage(); }
    public function updatedSumberFilter() { $this->resetPage(); }
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }

    public function resetFilter(): void
    {
        $this->cabangId = '';
        $this->sumberFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        $baseQuery = KasMutasi::query()
            ->when($this->cabangId, fn($q) => $q->where('cabang_id', $this->cabangId))
            ->when($this->sumberFilter, fn($q) => $q->where('sumber', $this->sumberFilter))
            ->when($this->dateFrom, fn($q) => $q->whereDate('tanggal', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('tanggal', '<=', $this->dateTo));

        $totalMasuk = $baseQuery->where('tipe', 'MASUK')->sum('jumlah');
        $totalKeluar = $baseQuery->where('tipe', 'KELUAR')->sum('jumlah');
        $netFlow = $totalMasuk - $totalKeluar;

        $query = $baseQuery->with(['cabang'])
            ->latest('tanggal')
            ->paginate(15);

        $listCabang = Cabang::where('is_aktif', true)->orderBy('nama_cabang')->get()->mapWithKeys(fn($c) => [$c->id => $c->nama_cabang])->toArray();

        return $this->view([
            'mutasis' => $query,
            'listCabang' => $listCabang,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'netFlow' => $netFlow,
        ])
        ->layout('layouts::app')
        ->title('Laporan Arus Kas');
    }
};
