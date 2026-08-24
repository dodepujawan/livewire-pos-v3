<?php

use App\Models\Barang;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $searchBarangKeyword = '';

    public function updatingSearchBarangKeyword()
    {
        $this->resetPage();
    }

    public function deleteBarang(int $barangId)
    {
        Barang::findOrFail($barangId)->delete();

        session()->flash(
            'success',
            'Barang berhasil dihapus'
        );
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
