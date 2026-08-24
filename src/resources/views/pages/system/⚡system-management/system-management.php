<?php

use App\Services\PermissionMatrixService;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public $roles;
    public ?int $selectedRoleId = null;
    public array $permissionMatrix = [];
    public array $selectedPermissions = [];
    public bool $showCreateRoleModal = false;
    public bool $showRenameRoleModal = false;
    public bool $showDeleteRoleModal = false;
    public string $newRoleName = '';
    public string $roleName = '';
    public string $search='';
    public array $actions = [];

    public function mount(): void
    {
        $this->roles = Role::orderBy('name')->get();

        $data = PermissionMatrixService::build();
        $this->permissionMatrix = $data['resources'];
        $this->actions = $data['actions'];

        $this->selectedRoleId = $this->roles->first()?->id;

        $this->loadPermissions();
    }

    public function updatedSelectedRoleId(): void
    {
        $this->loadPermissions();
    }

    protected function loadPermissions(): void
    {
        $role = Role::find($this->selectedRoleId);

        $this->selectedPermissions = $role
            ? $role->permissions()->pluck('name')->toArray()
            : [];
    }

    protected function refreshRoles(): void
    {
        $this->roles = Role::orderBy('name')->get();
    }

    protected function selectedRole(): ?Role
    {
        return Role::find($this->selectedRoleId);
    }

    public function selectAll(): void
    {
        $this->selectedPermissions = collect($this->permissionMatrix)
            ->flatMap(fn ($resource) => collect($resource['actions'])->values())
            ->unique()
            ->values()
            ->toArray();
    }

    public function clearAll(): void
    {
        $this->selectedPermissions = [];
    }

    public function getFilteredPermissionMatrixProperty()
    {
        if ($this->search === '') {
            return $this->permissionMatrix;
        }

        return collect($this->permissionMatrix)
            ->filter(function ($resource) {

                return str_contains(
                    strtolower($resource['label']),
                    strtolower($this->search)
                ) || str_contains(
                    strtolower($resource['resource']),
                    strtolower($this->search)
                );

            })
            ->values()
            ->toArray();
    }

    public function save(): void
    {
        $role = $this->selectedRole();

        if (! $role) {
            return;
        }

        $role->syncPermissions(
            collect($this->selectedPermissions)
                ->unique()
                ->values()
                ->toArray()
        );

        session()->flash('success', 'Permission berhasil disimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | Create Role
    |--------------------------------------------------------------------------
    */

    public function openCreateRoleModal(): void
    {
        $this->resetValidation();

        $this->newRoleName = '';

        $this->showCreateRoleModal = true;
    }

    public function createRole(): void
    {
        $validated = $this->validate([
            'newRoleName' => 'required|string|max:255|unique:roles,name',
        ]);

        $role = Role::create([
            'name' => $validated['newRoleName'],
            'guard_name' => 'web',
        ]);

        $this->refreshRoles();

        $this->selectedRoleId = $role->id;

        $this->showCreateRoleModal = false;

        $this->loadPermissions();

        session()->flash('success', 'Role berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | Rename Role
    |--------------------------------------------------------------------------
    */

    public function openRenameRoleModal(): void
    {
        $role = $this->selectedRole();

        if (! $role || $role->name === 'Super Admin') {
            return;
        }

        $this->resetValidation();

        $this->roleName = $role->name;

        $this->showRenameRoleModal = true;
    }

    public function renameRole(): void
    {
        $role = $this->selectedRole();

        if (! $role) {
            return;
        }

        if ($role->name === 'Super Admin') {
            session()->flash('error', 'Super Admin tidak boleh diubah.');

            return;
        }

        $validated = $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update([
            'name' => $validated['roleName'],
        ]);

        $this->refreshRoles();

        $this->showRenameRoleModal = false;

        session()->flash('success', 'Role berhasil diubah.');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Role
    |--------------------------------------------------------------------------
    */

    public function openDeleteRoleModal(): void
    {
        $role = $this->selectedRole();

        if (! $role || $role->name === 'Super Admin') {
            return;
        }

        $this->showDeleteRoleModal = true;
    }

    public function deleteRole(): void
    {
        $role = $this->selectedRole();

        if (! $role) {
            return;
        }

        if ($role->name === 'Super Admin') {
            session()->flash('error', 'Super Admin tidak boleh dihapus.');

            return;
        }

        if ($role->users()->exists()) {
            session()->flash('error', 'Role masih digunakan oleh user.');

            return;
        }

        $role->delete();

        $this->refreshRoles();

        $this->selectedRoleId = $this->roles->first()?->id;

        $this->showDeleteRoleModal = false;

        $this->loadPermissions();

        session()->flash('success', 'Role berhasil dihapus.');
    }
};
