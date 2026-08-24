<?php

use App\Models\Transaksi;
use Livewire\Component;

new class extends Component
{
    public int $transaksiId;
    public Transaksi $transaksi;

    public function mount(int $id): void
    {
        $this->transaksiId = $id;
        $this->transaksi = Transaksi::with(['details.barang', 'details.satuan'])
            ->findOrFail($id);
    }

    public function render()
    {
        return $this->view([])
            ->layout('layouts::app')
            ->title('Detail Transaksi');
    }
};