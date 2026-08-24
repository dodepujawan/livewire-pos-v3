<?php

use App\Models\Transaksi;
use App\Models\Barang;
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

    public function cancelTransaksi(int $id): void
    {
        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->status === 'BATAL') {
            session()->flash('error', 'Transaksi sudah dibatalkan');
            return;
        }

        try {
            \DB::transaction(function () use ($transaksi) {
                $transaksi->update(['status' => 'BATAL']);

                foreach ($transaksi->details as $detail) {
                    $barang = Barang::find($detail->barang_id);
                    if ($barang) {
                        $barang->increment('stok', $detail->qty_pcs);
                    }
                }
            });

            \DB::commit();

            session()->flash('success', 'Transaksi berhasil dibatalkan');
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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