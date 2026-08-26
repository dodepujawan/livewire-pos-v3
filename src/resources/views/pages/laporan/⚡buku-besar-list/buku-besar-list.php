<?php

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $searchKeyword = '';
    public string $akunTipe = '';
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

    public function updatingAkunTipe()
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
        $this->akunTipe = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        $jurnals = Jurnal::query()
            ->with(['details.akun', 'cabang'])
            ->when($this->searchKeyword, function ($query) {
                $query->where('nomor_jurnal', 'like', '%' . $this->searchKeyword . '%')
                      ->orWhere('keterangan', 'like', '%' . $this->searchKeyword . '%');
            })
            ->when($this->akunTipe, function ($query) {
                $query->whereHas('details', function ($q) {
                    $q->whereHas('akun', function ($aq) {
                        $aq->where('tipe', $this->akunTipe);
                    });
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('tanggal', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('tanggal', '<=', $this->dateTo);
            })
            ->latest('tanggal')
            ->paginate(15);

        $akunTypes = Akun::distinct()->pluck('tipe')->toArray();

        return $this->view([
            'jurnals' => $jurnals,
            'akunTypes' => $akunTypes,
        ])
        ->layout('layouts::app')
        ->title('Buku Besar');
    }
};
