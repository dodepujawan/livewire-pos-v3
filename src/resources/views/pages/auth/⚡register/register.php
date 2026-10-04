<?php

use App\Models\User;
use App\Models\Cabang;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public $regName = '';
    public $regEmail = '';
    public $regPassword = '';
    public $regRole = '';
    public $regCabangId = '';

    public array $cabangList = [];

    public function mount(): void
    {
        $this->loadCabangList();
    }

    private function loadCabangList(): void
    {
        $this->cabangList = Cabang::where('is_aktif', true)
            ->orderBy('nama_cabang')
            ->get()
            ->mapWithKeys(fn($c) => [$c->id => $c->nama_cabang])
            ->toArray();
    }

    public function register()
    {
        $this->validate([
            'regName' => 'required|string|max:255',
            'regEmail' => 'required|email|unique:users,email',
            'regPassword' => 'required|min:6',
            'regRole' => 'required',
            'regCabangId' => 'required|exists:cabang,id',
        ]);

        $user = User::create([
            'name' => $this->regName,
            'email' => $this->regEmail,
            'password' => Hash::make($this->regPassword),
            'cabang_id' => $this->regCabangId,
        ]);

        $user->assignRole($this->regRole);

        session()->flash('success', 'User berhasil dibuat');

        $this->reset(['regName', 'regEmail', 'regPassword', 'regRole', 'regCabangId']);
    }

    public function render()
    {
        return $this->view([
            'roles' => Role::all(),
            'cabangList' => $this->cabangList,
        ])
        ->layout('layouts.app')
        ->title('Register User');
    }
};
