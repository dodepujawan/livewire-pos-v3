<?php

use App\Models\Pembelian;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $searchKeyword = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $statusFilter = '';

    protected $rules = [
        'dateFrom' => 'nullable|date',
        'dateTo' => 'nullable|date|after_or_equal:dateFrom',
    ];

    public function updatingSearchKeyword()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->searchKeyword = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $pembelianData = Pembelian::query()
            ->with('cabang')
            ->when($this->searchKeyword, function ($query) {
                $query->where('nomor_beli', 'like', '%' . $this->searchKeyword . '%')
                      ->orWhere('supplier', 'like', '%' . $this->searchKeyword . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('tanggal', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('tanggal', '<=', $this->dateTo);
            })
            ->latest('tanggal')
            ->paginate(15);

        return $this->view([
            'pembelianData' => $pembelianData,
        ])
        ->layout('layouts::app')
        ->title('Pembelian Barang');
    }
};
