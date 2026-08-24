<?php

use App\Models\Transaksi;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $searchKeyword = '';
    public string $dateFrom = '';
    public string $dateTo = '';

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

    public function resetFilter(): void
    {
        $this->searchKeyword = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        $transaksiData = Transaksi::query()
            ->when($this->searchKeyword, function ($query) {
                $query->where('nomor_transaksi', 'like', '%' . $this->searchKeyword . '%');
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
            'transaksiData' => $transaksiData,
        ])
        ->layout('layouts::app')
        ->title('Transaksi Penjualan');
    }
};