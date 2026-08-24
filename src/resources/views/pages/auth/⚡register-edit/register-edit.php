<?php

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $userId;

    // prefix biar konsisten
    public $editName = '';
    public $editEmail = '';
    public $editRole = '';
    public $editPassword = '';

    public $roles = [];

    public function mount($id)
    {
        $authUser = Auth::user();

        // kalau bukan admin & bukan dirinya sendiri → blok
        if (!$authUser->hasRole('Super Admin') && $authUser->id != $id) {
            abort(403, 'Tidak punya akses');
        }

        $user = User::with('roles')->findOrFail($id);

        $this->userId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editRole = $user->getRoleNames()->first();

        // untuk menampilkan data roles di spatie ke select
        $this->roles = Role::pluck('name')->toArray();
    }

    public function update()
    {
        $authUser = Auth::user();

        if (!$authUser->hasRole('admin') && $authUser->id != $this->userId) {
            abort(403);
        }

        $this->validate();

        $user = User::findOrFail($this->userId);

        $data = [
            'name' => $this->editName,
            'email' => $this->editEmail,
        ];

        // kalau password diisi → update
        if (!empty($this->editPassword)) {
            $data['password'] = Hash::make($this->editPassword);
        }

        $user->update($data);

        // role (Spatie)
        $user->syncRoles([$this->editRole]);

        session()->flash('message', 'User berhasil diupdate');

        return $this->redirect(route('auth.register.list'), navigate: true);
    }

    // dia dipangil lewat $this->validate();
    public function rules()
    {
        return [
            'editName' => ['required', 'string', 'max:255'],

            'editEmail' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],

            'editRole' => ['required'],

            // password optional
            'editPassword' => ['nullable', 'min:6'],
        ];
    }

    public function render()
    {
        return $this->view([])
            ->layout('layouts::app')
            ->title('Edit User');
    }
};
