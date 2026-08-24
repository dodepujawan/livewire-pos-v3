<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Component;

new class extends Component
{
    public array $rolePermissions = [];
    public array $permissions = [];
    public array $roles = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->roles = Role::all()->pluck('name', 'id')->toArray();
        $this->permissions = Permission::all()->pluck('name', 'id')->toArray();

        foreach ($this->roles as $roleId => $roleName) {
            $role = Role::find($roleId);
            $this->rolePermissions[$roleId] = $role->permissions->pluck('id')->toArray();
        }
    }

    public function togglePermission(int $roleId, int $permissionId): void
    {
        $role = Role::findOrFail($roleId);
        $permission = Permission::findOrFail($permissionId);

        if (in_array($permissionId, $this->rolePermissions[$roleId])) {
            $role->revokePermissionTo($permission);
            $this->rolePermissions[$roleId] = array_diff($this->rolePermissions[$roleId], [$permissionId]);
        } else {
            $role->givePermissionTo($permission);
            $this->rolePermissions[$roleId][] = $permissionId;
        }
    }

    public function hasPermission(int $roleId, int $permissionId): bool
    {
        return in_array($permissionId, $this->rolePermissions[$roleId] ?? []);
    }

    public function render()
    {
        return $this->view()
            ->layout('layouts::app')
            ->title('Permission Matrix');
    }
};
