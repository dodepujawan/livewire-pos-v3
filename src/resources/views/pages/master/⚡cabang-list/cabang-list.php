<?php

use App\Models\Cabang;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected array $additionalPermissions = [
        'master.cabang.delete',
    ];

    public string $searchCabangKeyword = '';

    public function updatingSearchCabangKeyword()
    {
        $this->resetPage();
    }

    public function deleteCabang(int $cabangId)
    {
        Cabang::findOrFail($cabangId)->delete();

        session()->flash('success', 'Cabang berhasil dihapus');
    }

    public function render()
    {
        $cabangData = Cabang::query()
            ->when($this->searchCabangKeyword, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('kode_cabang', 'like', '%' . $this->searchCabangKeyword . '%')
                        ->orWhere('nama_cabang', 'like', '%' . $this->searchCabangKeyword . '%');
                });
            })
            ->latest()
            ->paginate(15);

        return $this->view([
            'cabangData' => $cabangData,
        ])
        ->layout('layouts::app')
        ->title('Master Cabang');
    }
};
