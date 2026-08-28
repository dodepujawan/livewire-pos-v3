<?php

use App\Models\Akun;
use App\Models\JurnalDetail;
use Livewire\Component;

new class extends Component
{
    public string $dateTo = '';

    protected $rules = [
        'dateTo' => 'nullable|date',
    ];

    public function updatedDateTo() {}

    public function render()
    {
        $dateTo = $this->dateTo ?: now()->format('Y-m-d');

        $akun = Akun::withCount(['jurnalDetails as debit_sum' => fn($q) => $q->where('debit', '>', 0)])
            ->withCount(['jurnalDetails as kredit_sum' => fn($q) => $q->where('kredit', '>', 0)])
            ->get()
            ->map(function ($akun) use ($dateTo) {
                $debit = JurnalDetail::where('akun_id', $akun->id)
                    ->join('jurnal', 'jurnal_id', '=', 'jurnal.id')
                    ->where('jurnal.tanggal', '<=', $dateTo)
                    ->sum('debit');

                $kredit = JurnalDetail::where('akun_id', $akun->id)
                    ->join('jurnal', 'jurnal_id', '=', 'jurnal.id')
                    ->where('jurnal.tanggal', '<=', $dateTo)
                    ->sum('kredit');

                return [
                    'id' => $akun->id,
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'tipe' => $akun->tipe,
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'saldo' => (float) $debit - (float) $kredit,
                ];
            })
            ->toArray();

        $aset = collect($akun)->where('tipe', 'ASET')->sum('saldo');
        $utang = collect($akun)->where('tipe', 'UTANG')->sum('saldo');
        $modal = collect($akun)->where('tipe', 'MODAL')->sum('saldo');
        $pendapatan = collect($akun)->where('tipe', 'PENDAPATAN')->sum('kredit');
        $beban = collect($akun)->where('tipe', 'BEBAN')->sum('debit');
        $laba = $pendapatan - $beban;

        $totalKanan = $utang + $modal + $laba;

        return $this->view([
            'akunList' => $akun,
            'aset' => $aset,
            'utang' => $utang,
            'modal' => $modal,
            'pendapatan' => $pendapatan,
            'beban' => $beban,
            'laba' => $laba,
            'totalKanan' => $totalKanan,
            'dateTo' => $dateTo,
        ])
        ->layout('layouts::app')
        ->title('Laporan Neraca');
    }
};
