<?php

use App\Models\Cabang;
use Livewire\Component;

new class extends Component
{
    public string $createCabangKode = '';
    public string $createCabangNama = '';
    public string $createCabangAlamat = '';
    public bool $createCabangAktif = true;

    public function saveCabang()
    {
        $this->validate([
            'createCabangKode' => 'required|unique:cabang,kode_cabang',
            'createCabangNama' => 'required|min:3',
            'createCabangAlamat' => 'nullable|string',
            'createCabangAktif' => 'boolean',
        ]);

        Cabang::create([
            'kode_cabang' => strtoupper($this->createCabangKode),
            'nama_cabang' => $this->createCabangNama,
            'alamat' => $this->createCabangAlamat,
            'is_aktif' => $this->createCabangAktif,
        ]);

        session()->flash('success', 'Cabang berhasil ditambahkan');

        return $this->redirect(route('master.cabang.list'), navigate: true);
    }

    public function render()
    {
        return $this->view([])
            ->layout('layouts::app')
            ->title('Tambah Cabang');
    }
};
