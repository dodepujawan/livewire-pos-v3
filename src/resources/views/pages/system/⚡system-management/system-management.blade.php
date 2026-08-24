<div>
    @if (session()->has('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <x-form.card>
        {{-- Header --}}
        <div class="mb-6 flex items-end justify-between">
            <div class="flex items-end gap-3">
                <div class="w-80">
                    <x-form.select
                        label="Role"
                        name="selectedRoleId"
                        wire:model.live="selectedRoleId"
                    >
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <button
                    type="button"
                    wire:click="openRenameRoleModal"
                    class="mb-5 inline-flex h-11 w-11 items-center justify-center rounded-lg border border-gray-300 bg-white text-lg hover:bg-gray-50"
                >
                    ✏️
                </button>
                <button
                    type="button"
                    wire:click="openDeleteRoleModal"
                    class="mb-5 inline-flex h-11 w-11 items-center justify-center rounded-lg border border-red-300 bg-white text-lg text-red-600 hover:bg-red-50"
                >
                    🗑️
                </button>
            </div>
            <div class="mb-5 flex items-center gap-2">
                <button
                    type="button"
                    wire:click="openCreateRoleModal"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50"
                >
                    + Role
                </button>
                <button
                    type="button"
                    wire:click="save"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Simpan
                </button>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="mb-4 flex items-center gap-2">
            <button wire:click="selectAll" class="rounded border px-3 py-2 text-sm hover:bg-gray-50">
                ☑ Pilih Semua
            </button>
            <button wire:click="clearAll" class="rounded border px-3 py-2 text-sm hover:bg-gray-50">
                ☐ Kosongkan
            </button>
        </div>
        <div class="mb-4">
            <x-form.input label="Cari Permission" name="search" wire:model.live.debounce.300ms="search" />
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200">
            <div class="max-h-[70vh] overflow-auto">
                <table class="min-w-full">
                    <thead class="sticky top-0 z-20 bg-gray-100 shadow-sm">
                    <tr>
                        <th class="w-72">
                            MENU
                        </th>
                        @foreach($actions as $action)
                            <th class="w-28 px-4 py-4 text-center">
                                {{ strtoupper($action) }}
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($this->filteredPermissionMatrix as $resource)
                            <tr class="transition hover:bg-blue-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">{{ $resource['label'] }}</div>
                                    <div class="mt-1 text-sm text-gray-400">{{ $resource['resource'] }}</div>
                                </td>
                                @foreach($actions as $action)
                                    @php
                                        $permission = $resource['actions'][$action] ?? null;
                                    @endphp
                                    <td class="text-center">
                                        @if($permission)
                                            <input
                                                type="checkbox"
                                                wire:model="selectedPermissions"
                                                value="{{ $permission }}"
                                            >
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-gray-500">
                                    Tidak ada permission.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-form.card>
    {{-- Create Role Modal --}}
    @if ($showCreateRoleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            wire:click.self="$set('showCreateRoleModal', false)">
            <form wire:submit="createRole" class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold">Tambah Role</h2>
                <div class="mt-4">
                    <label class="mb-2 block text-sm font-medium">Nama Role</label>
                    <input type="text" wire:model.defer="newRoleName"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                        autofocus>
                    @error('newRoleName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showCreateRoleModal', false)"
                        class="rounded-lg border border-gray-300 px-4 py-2">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Rename Role Modal --}}
    @if ($showRenameRoleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            wire:click.self="$set('showRenameRoleModal', false)">
            <form wire:submit="renameRole" class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold">Rename Role</h2>
                <div class="mt-4">
                    <label class="mb-2 block text-sm font-medium">Nama Role</label>
                    <input type="text" wire:model.defer="roleName"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    @error('roleName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showRenameRoleModal', false)"
                        class="rounded-lg border border-gray-300 px-4 py-2">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Delete Role Modal --}}
    @if ($showDeleteRoleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            wire:click.self="$set('showDeleteRoleModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-red-600">Hapus Role</h2>
                <p class="mt-3 text-sm text-gray-600">
                    Role ini akan dihapus secara permanen. Apakah Anda yakin?
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showDeleteRoleModal', false)"
                        class="rounded-lg border border-gray-300 px-4 py-2">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteRole"
                        class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif
    <x-form.loading wire:target="save, createRole, renameRole, deleteRole"/>
</div>
