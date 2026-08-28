<?php

use App\Models\Cabang;
use App\Models\Pelunasan;
use App\Models\Piutang;
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
    public ?int $selectedPiutangId = null;
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

    public function openPelunasanModal(int $piutangId): void
    {
        $this->selectedPiutangId = $piutangId;
        $this->pelunasanJumlah = 0;
        $this->pelunasanMetodeBayar = 'TUNAI';
        $this->pelunasanCatatan = '';
        $this->showPelunasanModal = true;
    }

    public function closePelunasanModal(): void
    {
        $this->showPelunasanModal = false;
        $this->selectedPiutangId = null;
    }

    public function processPelunasan(): void
    {
        $this->validate();

        $piutang = Piutang::findOrFail($this->selectedPiutangId);

        if ((float) $this->pelunasanJumlah > (float) $piutang->sisa) {
            session()->flash('error', 'Jumlah pelunasan melebihi sisa piutang');
            return;
        }

        try {
            $pelunasan = Pelunasan::create([
                'cabang_id' => $piutang->cabang_id,
                'jenis' => 'PIUTANG',
                'referensi_id' => $piutang->id,
                'tanggal' => now()->format('Y-m-d'),
                'jumlah' => $this->pelunasanJumlah,
                'metode_bayar' => $this->pelunasanMetodeBayar,
                'catatan' => $this->pelunasanCatatan,
            ]);

            PelunasanService::processPelunasanPiutang($pelunasan, (float) $this->pelunasanJumlah);

            session()->flash('success', 'Pelunasan berhasil diproses');
            $this->closePelunasanModal();
            $this->redirect(route('transaksi.piutang.list'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Piutang::query()
            ->with(['cabang', 'pelunasan'])
            ->when($this->searchKeyword, function ($query) {
                $query->where('customer', 'like', '%' . $this->searchKeyword . '%')
                      ->orWhere('nomor_piutang', 'like', '%' . $this->searchKeyword . '%');
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
            'piutangs' => $query,
            'listCabang' => $listCabang,
        ])
        ->layout('layouts::app')
        ->title('Daftar Piutang');
    }
};
