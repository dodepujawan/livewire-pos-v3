<?php

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use WithPagination;

    public string $searchKeyword = '';

    public function updatingSearchKeyword()
    {
        $this->resetPage();
    }

    public function delete(int $roleId)
    {
        $role = Role::findOrFail($roleId);

        if ($role->name === 'Super Admin') {
            session()->flash('error', 'Super Admin tidak boleh dihapus.');
            return;
        }

        if ($role->users()->count() > 0) {
            session()->flash('error', 'Role masih digunakan.');
            return;
        }

        $role->delete();

        session()->flash('success', 'Role berhasil dihapus.');
    }

    public function render()
    {
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when(
                $this->searchKeyword,
                fn ($query) => $query->where(
                    'name',
                    'like',
                    "%{$this->searchKeyword}%"
                )
            )
            ->orderBy('name')
            ->paginate(10);

        return $this->view([
            'roles' => $roles,
        ])
        ->layout('layouts::app')
        ->title('Role Management');
    }
};
