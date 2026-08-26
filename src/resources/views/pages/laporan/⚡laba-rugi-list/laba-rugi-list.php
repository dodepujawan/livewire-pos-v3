<?php

use App\Models\Akun;
use App\Models\JurnalDetail;
use Livewire\Component;

new class extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    protected $rules = [
        'dateFrom' => 'nullable|date',
        'dateTo' => 'nullable|date|after_or_equal:dateFrom',
    ];

    public function resetFilter(): void
    {
        $this->dateFrom = '';
        $this->dateTo = '';
    }

    public function render()
    {
        $query = JurnalDetail::query()
            ->with('akun')
            ->whereHas('jurnal', function ($q) {
                if ($this->dateFrom) {
                    $q->whereDate('tanggal', '>=', $this->dateFrom);
                }
                if ($this->dateTo) {
                    $q->whereDate('tanggal', '<=', $this->dateTo);
                }
            });

        $akunPendapatan = Akun::where('tipe', 'PENDAPATAN')->get();
        $akunBeban = Akun::where('tipe', 'BEBAN')->get();

        $pendapatanTotal = 0;
        $bebanTotal = 0;
        $labaRugi = [];

        foreach ($akunPendapatan as $akun) {
            $total = $query->clone()
                ->where('akun_id', $akun->id)
                ->sum('kredit');

            if ($total > 0) {
                $labaRugi[] = [
                    'akun_id' => $akun->id,
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'tipe' => 'PENDAPATAN',
                    'debit' => 0,
                    'kredit' => $total,
                ];
                $pendapatanTotal += $total;
            }
        }

        foreach ($akunBeban as $akun) {
            $total = $query->clone()
                ->where('akun_id', $akun->id)
                ->sum('debit');

            if ($total > 0) {
                $labaRugi[] = [
                    'akun_id' => $akun->id,
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'tipe' => 'BEBAN',
                    'debit' => $total,
                    'kredit' => 0,
                ];
                $bebanTotal += $total;
            }
        }

        $labaRugiNeto = $pendapatanTotal - $bebanTotal;

        return $this->view([
            'labaRugi' => $labaRugi,
            'pendapatanTotal' => $pendapatanTotal,
            'bebanTotal' => $bebanTotal,
            'labaRugiNeto' => $labaRugiNeto,
        ])
        ->layout('layouts::app')
        ->title('Laba Rugi');
    }
};
