<?php

use App\Models\Cabang;
use Livewire\Component;

new class extends Component
{
    public int $editCabangId;
    public string $editCabangKode = '';
    public string $editCabangNama = '';
    public string $editCabangAlamat = '';
    public bool $editCabangAktif = true;

    public function mount($id)
    {
        $cabang = Cabang::findOrFail($id);
        $this->editCabangId = $cabang->id;
        $this->editCabangKode = $cabang->kode_cabang;
        $this->editCabangNama = $cabang->nama_cabang;
        $this->editCabangAlamat = $cabang->alamat ?? '';
        $this->editCabangAktif = $cabang->is_aktif;
    }

    public function updateCabang()
    {
        $this->validate([
            'editCabangKode' => 'required|unique:cabang,kode_cabang,' . $this->editCabangId,
            'editCabangNama' => 'required|min:3',
            'editCabangAlamat' => 'nullable|string',
            'editCabangAktif' => 'boolean',
        ]);

        $cabang = Cabang::findOrFail($this->editCabangId);
        $cabang->update([
            'kode_cabang' => strtoupper($this->editCabangKode),
            'nama_cabang' => $this->editCabangNama,
            'alamat' => $this->editCabangAlamat,
            'is_aktif' => $this->editCabangAktif,
        ]);

        session()->flash('success', 'Cabang berhasil diperbarui');

        return $this->redirect(route('master.cabang.list'), navigate: true);
    }

    public function render()
    {
        return $this->view([])
            ->layout('layouts::app')
            ->title('Edit Cabang');
    }
};
