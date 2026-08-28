<?php

use App\Models\Cabang;
use App\Models\Transaksi;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $searchKeyword = '';
    public string $cabangId = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $statusFilter = '';

    protected $rules = [
        'dateFrom' => 'nullable|date',
        'dateTo' => 'nullable|date|after_or_equal:dateFrom',
    ];

    public function updatedSearchKeyword() { $this->resetPage(); }
    public function updatedCabangId() { $this->resetPage(); }
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }

    public function resetFilter(): void
    {
        $this->searchKeyword = '';
        $this->cabangId = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $baseQuery = Transaksi::query()
            ->when($this->searchKeyword, function ($q) {
                $q->where('nomor_transaksi', 'like', '%' . $this->searchKeyword . '%')
                  ->orWhere('customer', 'like', '%' . $this->searchKeyword . '%');
            })
            ->when($this->cabangId, fn($q) => $q->where('cabang_id', $this->cabangId))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFrom, fn($q) => $q->whereDate('tanggal', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('tanggal', '<=', $this->dateTo));

        $totalPenjualan = $baseQuery->sum('grand_total');
        $totalPajak = $baseQuery->sum('pajak');
        $totalDiskon = $baseQuery->sum('diskon_total');

        $query = $baseQuery->with(['cabang'])
            ->latest('tanggal')
            ->paginate(15);

        $listCabang = Cabang::where('is_aktif', true)->orderBy('nama_cabang')->get()->mapWithKeys(fn($c) => [$c->id => $c->nama_cabang])->toArray();

        return $this->view([
            'transaksis' => $query,
            'listCabang' => $listCabang,
            'totalPenjualan' => $totalPenjualan,
            'totalPajak' => $totalPajak,
            'totalDiskon' => $totalDiskon,
        ])
        ->layout('layouts::app')
        ->title('Laporan Penjualan');
    }
};
