<?php

use App\Models\Cabang;
use App\Models\Hutang;
use App\Models\Pelunasan;
use App\Services\PelunasanService;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $searchKeyword = '';
    public string $cabangId = '';
    public string $statusFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    // Pelunasan modal
    public bool $showPelunasanModal = false;
    public ?int $selectedHutangId = null;
    public $pelunasanJumlah = 0;
    public string $pelunasanMetodeBayar = 'TUNAI';
    public string $pelunasanCatatan = '';

    protected $rules = [
        'dateFrom' => 'nullable|date',
        'dateTo' => 'nullable|date|after_or_equal:dateFrom',
        'pelunasanJumlah' => 'required|numeric|min:0.01',
        'pelunasanMetodeBayar' => 'required|in:TUNAI,TRANSFER,QRIS',
    ];

    public function updatedSearchKeyword()
    {
        $this->resetPage();
    }

    public function updatedCabangId()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->searchKeyword = '';
        $this->cabangId = '';
        $this->statusFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function openPelunasanModal(int $hutangId): void
    {
        $this->selectedHutangId = $hutangId;
        $this->pelunasanJumlah = 0;
        $this->pelunasanMetodeBayar = 'TUNAI';
        $this->pelunasanCatatan = '';
        $this->showPelunasanModal = true;
    }

    public function closePelunasanModal(): void
    {
        $this->showPelunasanModal = false;
        $this->selectedHutangId = null;
    }

    public function processPelunasan(): void
    {
        $this->validate();

        $hutang = Hutang::findOrFail($this->selectedHutangId);

        if ((float) $this->pelunasanJumlah > (float) $hutang->sisa) {
            session()->flash('error', 'Jumlah pelunasan melebihi sisa hutang');
            return;
        }

        try {
            $pelunasan = Pelunasan::create([
                'cabang_id' => $hutang->cabang_id,
                'jenis' => 'HUTANG',
                'referensi_id' => $hutang->id,
                'tanggal' => now()->format('Y-m-d'),
                'jumlah' => $this->pelunasanJumlah,
                'metode_bayar' => $this->pelunasanMetodeBayar,
                'catatan' => $this->pelunasanCatatan,
            ]);

            PelunasanService::processPelunasanHutang($pelunasan, (float) $this->pelunasanJumlah);

            session()->flash('success', 'Pelunasan berhasil diproses');
            $this->closePelunasanModal();
            $this->redirect(route('transaksi.hutang.list'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Hutang::query()
            ->with(['cabang', 'pembelian', 'pelunasan'])
            ->when($this->searchKeyword, function ($query) {
                $query->where('supplier', 'like', '%' . $this->searchKeyword . '%')
                      ->orWhere('nomor_hutang', 'like', '%' . $this->searchKeyword . '%');
            })
            ->when($this->cabangId, function ($query) {
                $query->where('cabang_id', $this->cabangId);
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

        $listCabang = Cabang::where('is_aktif', true)
            ->orderBy('nama_cabang')
            ->get()
            ->mapWithKeys(fn($c) => [$c->id => $c->nama_cabang])
            ->toArray();

        return $this->view([
            'hutangs' => $query,
            'listCabang' => $listCabang,
        ])
        ->layout('layouts::app')
        ->title('Daftar Hutang');
    }
};
