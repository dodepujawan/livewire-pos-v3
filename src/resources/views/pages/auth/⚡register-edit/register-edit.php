<?php

use App\Models\User;
use App\Models\Cabang;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $userId;

    public $editName = '';
    public $editEmail = '';
    public $editRole = '';
    public $editPassword = '';
    public $editCabangId = '';

    public $roles = [];
    public array $cabangList = [];

    public function mount($id)
    {
        $authUser = Auth::user();

        if (!$authUser->hasRole('Super Admin') && $authUser->id != $id) {
            abort(403, 'Tidak punya akses');
        }

        $user = User::with('roles')->findOrFail($id);

        $this->userId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editRole = $user->getRoleNames()->first();
        $this->editCabangId = $user->cabang_id ?? '';

        $this->roles = Role::pluck('name')->toArray();
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

    public function update()
    {
        $authUser = Auth::user();

        if (!$authUser->hasRole('admin') && $authUser->id != $this->userId) {
            abort(403);
        }

        $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editEmail' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'editRole' => ['required'],
            'editCabangId' => ['required', 'exists:cabang,id'],
            'editPassword' => ['nullable', 'min:6'],
        ]);

        $user = User::findOrFail($this->userId);

        $data = [
            'name' => $this->editName,
            'email' => $this->editEmail,
            'cabang_id' => $this->editCabangId,
        ];

        if (!empty($this->editPassword)) {
            $data['password'] = Hash::make($this->editPassword);
        }

        $user->update($data);
        $user->syncRoles([$this->editRole]);

        session()->flash('message', 'User berhasil diupdate');

        return $this->redirect(route('auth.register.list'), navigate: true);
    }

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
            'editCabangId' => ['required', 'exists:cabang,id'],
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
