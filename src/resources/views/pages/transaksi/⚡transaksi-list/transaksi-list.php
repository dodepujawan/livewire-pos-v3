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
    public string $statusFilter = '';
    public ?int $cancelTransaksiId = null;
    public string $cancelReason = '';
    public bool $showCancelModal = false;

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

    public function updatingStatusFilter(): void
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

    public function openCancelModal(int $id): void
    {
        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->status === 'BATAL') {
            session()->flash('error', 'Transaksi sudah dibatalkan');
            return;
        }

        if ($transaksi->status === 'DRAFT') {
            // Draft tidak memerlukan alasan dari kasir karena belum berdampak
            // pada stok atau laporan final.
            $this->cancelTransaksiId = $id;
            $this->cancelReason = 'DRAFT_CLEARED';
            $this->confirmCancelTransaksi();
            return;
        }

        $this->cancelTransaksiId = $id;
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancelTransaksiId = null;
        $this->cancelReason = '';
    }

    public function confirmCancelTransaksi(): void
    {
        $this->validate([
            'cancelTransaksiId' => 'required|exists:transaksi,id',
            'cancelReason' => 'required|string|min:3|max:500',
        ]);

        $transaksi = Transaksi::findOrFail($this->cancelTransaksiId);
        $isDraft = $transaksi->status === 'DRAFT';

        try {
            $reason = $this->cancelReason;

            \DB::transaction(function () use ($transaksi, $reason) {
                if ($transaksi->status === 'DRAFT') {
                    // Draft belum pernah mengurangi stok, jadi cukup soft-delete.
                    $transaksi->update([
                        'deleted_at' => now(),
                        'deleted_by' => auth()->id(),
                        'delete_reason' => $reason,
                    ]);

                    return;
                }

                $transaksi->update(['status' => 'BATAL']);
                $transaksi->update(['delete_reason' => $reason]);

                foreach ($transaksi->details as $detail) {
                    $barang = Barang::find($detail->barang_id);
                    if ($barang) {
                        $barang->increment('stok', $detail->qty_pcs);
                    }
                }
            });

            $this->closeCancelModal();
            session()->flash(
                'success',
                $isDraft
                    ? 'Draft berhasil dihapus.'
                    : 'Transaksi berhasil dibatalkan dengan alasan: ' . $reason
            );
            $this->redirect(route('transaksi.penjualan.list'), navigate: true);
        } catch (\Exception $e) {
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
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
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
