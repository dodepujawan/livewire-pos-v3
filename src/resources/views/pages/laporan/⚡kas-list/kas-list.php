<?php

use App\Models\Cabang;
use App\Models\KasMutasi;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $cabangId = 0;
    public string $tanggalDari = '';
    public string $tanggalSampai = '';

    public array $listCabang = [];

    protected $rules = [
        'tanggalDari' => 'nullable|date',
        'tanggalSampai' => 'nullable|date|after_or_equal:tanggalDari',
    ];

    public function mount(): void
    {
        $this->loadCabangList();
        $this->setDefaultCabang();
    }

    private function loadCabangList(): void
    {
        $this->listCabang = Cabang::where('is_aktif', true)
            ->orderBy('nama_cabang')
            ->get()
            ->mapWithKeys(fn($c) => [$c->id => $c->nama_cabang])
            ->toArray();
    }

    private function setDefaultCabang(): void
    {
        if (!empty($this->listCabang)) {
            $this->cabangId = array_key_first($this->listCabang);
        }
    }

    public function updatingCabangId()
    {
        $this->resetPage();
    }

    public function updatingTanggalDari()
    {
        $this->resetPage();
    }

    public function updatingTanggalSampai()
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->cabangId = array_key_first($this->listCabang);
        $this->tanggalDari = '';
        $this->tanggalSampai = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = KasMutasi::query()
            ->with('cabang')
            ->when($this->cabangId, function ($query) {
                $query->where('cabang_id', $this->cabangId);
            })
            ->when($this->tanggalDari, function ($query) {
                $query->whereDate('tanggal', '>=', $this->tanggalDari);
            })
            ->when($this->tanggalSampai, function ($query) {
                $query->whereDate('tanggal', '<=', $this->tanggalSampai);
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $kasData = $query->paginate(20);

        return $this->view([
            'kasData' => $kasData,
        ])
        ->layout('layouts::app')
        ->title('Laporan Kas');
    }
};
