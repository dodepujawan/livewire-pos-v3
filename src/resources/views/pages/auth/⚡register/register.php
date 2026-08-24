<?php

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public $regName = '';
    public $regEmail = '';
    public $regPassword = '';
    public $regRole = '';

    public function register()
    {
        $this->validate([
            'regName' => 'required|string|max:255',
            'regEmail' => 'required|email|unique:users,email',
            'regPassword' => 'required|min:6',
            'regRole' => 'required'
        ]);

        $user = User::create([
            'name' => $this->regName,
            'email' => $this->regEmail,
            'password' => Hash::make($this->regPassword),
        ]);

        // assign role spatie
        $user->assignRole($this->regRole);

        session()->flash('success', 'User berhasil dibuat');

        // reset form
        $this->reset(['regName', 'regEmail', 'regPassword', 'regRole']);
    }

    public function render()
    {
        return $this->view([
            'roles' => Role::all()
        ])
        ->layout('layouts.app')
        ->title('Register User');
    }
};
